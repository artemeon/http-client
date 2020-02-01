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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Implementation of the psr7 RequestInterface
 */
class Request extends Message implements RequestInterface
{
    /** @var string */
    private $method;

    /** @var Uri */
    private $url;

    /** @var string */
    private $requestTarget;

    /** @var string */
    public const METHOD_POST = 'POST';

    /** @var string */
    public const METHOD_GET = 'GET';

    /** @var string */
    public const METHOD_PUT = 'PUT';

    /** @var string */
    public const METHOD_DELETE = 'DELETE';

    /** @var string */
    public const METHOD_OPTIONS = 'OPTIONS';

    /** @var string */
    public const METHOD_PATCH = 'PATCH';

    /**
     * Request constructor.
     *
     * @param string $method
     * @param Uri $url
     * @param Headers|null $headers
     * @param StreamInterface|null $body
     * @param string $version
     * @throws InvalidArgumentException
     */
    private function __construct(
        string $method,
        Uri $url,
        ?Headers $headers = null,
        ?StreamInterface $body = null,
        string $version = '1.1'
    ) {
        $this->url = $url;
        $this->requestTarget = $this->parseRequestTarget($url);
        $this->assertValidMethod($method);
        $this->method = $method;

        parent::__construct(
            $this->addHostHeader($url, $headers),
            $body,
            $version
        );
    }

    /**
     * Named constructor to create an instance for post requests
     *
     * @param Uri $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forGet(Uri $url, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_GET,
            $url,
            $headers,
            null,
            $version
        );
    }

    /**
     * Named constructor to create an instance for OPTIONS requests
     *
     * @param Uri $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forOptions(Uri $url, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_OPTIONS,
            $url,
            $headers,
            null,
            $version
        );
    }

    /**
     * Named constructor to create an instance for POST requests
     *
     * @param Uri $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forPost(Uri $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_POST,
            $url,
            $headers,
            $body->getStream(),
            $version
        );
    }

    /**
     * Named constructor to create an instance for PUT requests
     *
     * @param Uri $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forPut(Uri $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_PUT,
            $url,
            $headers,
            $body->getStream(),
            $version
        );
    }

    /**
     * Named constructor to create an instance for PATCH requests
     *
     * @param Uri $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: the http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forPatch(Uri $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
    {
        $headers = self::addHeaderFromBody($body, $headers);

        return new self(
            self::METHOD_PATCH,
            $url,
            $headers,
            $body->getStream(),
            $version
        );
    }

    /**
     * Named constructor to create an instance for DELETE requests
     *
     * @param Uri $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param string $version Optional: http protocol version string
     * @throws InvalidArgumentException
     */
    public static function forDelete(Uri $url, ?Headers $headers = null, string $version = '1.1'): self
    {
        return new self(
            self::METHOD_DELETE,
            $url,
            $headers,
            null,
            $version
        );
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method): self
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException('method must be a string value');
        }

        $this->assertValidMethod($method);

        $cloned = clone $this;
        $cloned->method = $method;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $cloned = clone $this;
        $cloned->url = $uri;

        $newHost = Header::fromString(HeaderField::HOST, $uri->getHost());

        if ($preserveHost === true) {
            // Update only if the Host header is missing or empty, and the new URI contains a host component
            if ($cloned->headers->isEmpty(HeaderField::HOST) || !empty($uri->getHost())) {
                $cloned->headers->replaceHeader($newHost);
            }
        } elseif (!empty($uri->getHost())) {
            // Default: Update the Host header if the URI contains a host component
            $cloned->headers->replaceHeader($newHost);
        }

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget): self
    {
        $cloned = clone $this;
        $cloned->requestTarget = trim(strval($requestTarget));

        return $cloned;
    }

    /**
     * Add the calculated header fields from the body to the headers collection
     *
     * @param Body $body
     * @param Headers|null $headers
     * @throws InvalidArgumentException
     */
    private static function addHeaderFromBody(Body $body, ?Headers $headers): Headers
    {
        $headers = $headers ?? Headers::create();
        $headers->addHeader(Header::fromField(ContentType::fromString($body->getMimeType())));
        $headers->addHeader(Header::fromField(ContentLength::fromInt($body->getContentLength())));

        return $headers;
    }

    /**
     * Add the host header based on the given Url
     *
     * @param Uri $uri
     * @param Headers|null $headers
     * @throws InvalidArgumentException
     */
    private function addHostHeader(Uri $uri, ?Headers $headers): Headers
    {
        if ($headers instanceof Headers) {
            $headers->addHeader(Header::fromField(Host::fromUri($uri)));
        } else {
            $headers = Headers::fromFields([Host::fromUri($uri)]);
        }

        return $headers;
    }

    /**
     * Checks request method is valid
     *
     * @param string $method
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
        ];

        if (!in_array($method, $validMethods)) {
            throw new InvalidArgumentException("method: $method is invalid");
        }
    }

    /**
     * @return string
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
