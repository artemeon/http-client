<?php

/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Artemeon\HttpClient\Http;

use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Header\Fields\ContentLength;
use Artemeon\HttpClient\Http\Header\Fields\ContentType;
use Artemeon\HttpClient\Http\Header\Fields\Host;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Implementation of the psr7 RequestInterface.
 */
class Request extends Message implements RequestInterface
{
    public const string METHOD_POST = 'POST';

    public const string METHOD_GET = 'GET';

    public const string METHOD_PUT = 'PUT';

    public const string METHOD_DELETE = 'DELETE';

    public const string METHOD_OPTIONS = 'OPTIONS';

    public const string METHOD_PATCH = 'PATCH';

    public const string METHOD_HEAD = 'HEAD';

    private string $method;

    private ?string $requestTarget = null;

    /**
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $method,
        private UriInterface $uri,
        ?Headers $headers = null,
        ?StreamInterface $body = null,
        string $version = '1.1',
    ) {
        $this->assertValidMethod($method);
        $this->method = $method;

        parent::__construct(
            $this->addHostHeader($this->uri, $headers),
            $body,
            $version,
        );
    }

    /**
     * Named constructor to create an instance for GET requests.
     *
     * @param Uri $uri The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forGet(Uri $uri, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_GET,
            $uri,
            $headers,
            null,
            $version,
        );
    }

    /**
     * Named constructor to create an instance for OPTIONS requests.
     *
     * @param Uri $uri The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forOptions(Uri $uri, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_OPTIONS,
            $uri,
            $headers,
            null,
            $version,
        );
    }

    /**
     * Named constructor to create an instance for POST requests.
     *
     * @param Uri $uri The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forPost(Uri $uri, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_POST,
            $uri,
            $headers,
            $body->getStream(),
            $version,
        );
    }

    /**
     * Named constructor to create an instance for PUT requests.
     *
     * @param Uri $uri The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forPut(Uri $uri, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_PUT,
            $uri,
            $headers,
            $body->getStream(),
            $version,
        );
    }

    /**
     * Named constructor to create an instance for PATCH requests.
     *
     * @param Uri $uri The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forPatch(Uri $uri, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_PATCH,
            $uri,
            $headers,
            $body->getStream(),
            $version,
        );
    }

    /**
     * Named constructor to create an instance for DELETE requests.
     *
     * @param Uri $uri The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: http protocol version string
     *
     * @throws InvalidArgumentException
     */
    public static function forDelete(Uri $uri, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_DELETE,
            $uri,
            $headers,
            null,
            $version,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withMethod(string $method): self
    {
        $this->assertValidMethod($method);

        $cloned = clone $this;
        $cloned->method = $method;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     */
    #[Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): self
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $normalizedPath = preg_replace('#^/+#', '/', $uri->getPath());
        $cloned = clone $this;
        $cloned->uri = $uri->withPath($normalizedPath);

        $newHost = Header::fromString(HeaderField::HOST, $uri->getHost());

        if ($preserveHost === true) {
            // Update only if the Host header is missing or empty, and the new URI contains a host component
            if ($cloned->headers->isEmpty(HeaderField::HOST) && !empty($uri->getHost())) {
                $cloned->headers->replace($newHost);
            }
        } elseif (!empty($uri->getHost())) {
            // Default: Update the Host header if the URI contains a host component
            $cloned->headers->replace($newHost);
        }

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        return $this->parseRequestTarget($this->uri);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withRequestTarget(string $requestTarget): self
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace',
            );
        }

        $cloned = clone $this;
        $cloned->requestTarget = trim((string) $requestTarget);

        return $cloned;
    }

    /**
     * Add the calculated header fields from the body to the headers collection.
     *
     * @throws InvalidArgumentException
     */
    private static function addHeaderFromBody(Body $body, ?Headers $headers): Headers
    {
        $headers ??= Headers::create();
        $headers->add(Header::fromField(ContentType::fromString($body->getMimeType())));
        $headers->add(Header::fromField(ContentLength::fromInt($body->getContentLength())));

        return $headers;
    }

    /**
     * Add the host header based on the given Url.
     *
     * @throws InvalidArgumentException
     */
    private function addHostHeader(UriInterface $uri, ?Headers $headers): Headers
    {
        if ($headers instanceof Headers) {
            $headers->add(Header::fromField(Host::fromUri($uri)));
        } else {
            $headers = Headers::fromFields([Host::fromUri($uri)]);
        }

        return $headers;
    }

    /**
     * Checks for valid request methods.
     *
     * @throws InvalidArgumentException
     */
    private function assertValidMethod(string $method): void
    {
        $validMethods = [
            self::METHOD_DELETE,
            self::METHOD_GET,
            self::METHOD_OPTIONS,
            self::METHOD_PATCH,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_HEAD,
        ];

        if (!in_array(strtoupper($method), $validMethods)) {
            throw new InvalidArgumentException("method: $method is invalid");
        }
    }

    /**
     * Parse the standard request target from the given Uri.
     */
    private function parseRequestTarget(UriInterface $uri): string
    {
        $target = $uri->getPath();

        if (empty($target)) {
            $target = '/';
        }

        if ($uri->getQuery() != '') {
            $target .= '?' . $uri->getQuery();
        }

        return $target;
    }
}
