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

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Header\Fields\ContentLength;
use Artemeon\HttpClient\Http\Header\Fields\ContentType;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Psr7\RequestInterfaceSubset;
use Artemeon\HttpClient\Psr7\UriInterfaceSubset;
use Psr\Http\Message\StreamInterface;

/**
 * Partial implementation of the psr7 RequestInterface
 */
class Request extends Message implements RequestInterfaceSubset
{
    /** @var string */
    private $method;

    /** @var Url */
    private $url;

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
     * @param Url $url
     * @param Headers|null $headers
     * @param Body|null $body
     * @param string $version
     * @throws HttpClientException
     */
    private function __construct(
        string $method,
        Url $url,
        ?Headers $headers = null,
        ?StreamInterface $body = null,
        string $version = '1.1'
    ) {
        $this->method = $method;
        $this->url = $url;

        parent::__construct(
            $headers,
            $body,
            $version
        );
    }

    /**
     * Named constructor to create an instance for post requests
     *
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: http protocol version string
     * @throws HttpClientException
     */
    public static function forGet(Url $url, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: the http protocol version string
     * @throws HttpClientException
     */
    public static function forOptions(Url $url, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Url $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: the http protocol version string
     * @throws HttpClientException
     */
    public static function forPost(Url $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Url $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: the http protocol version string
     * @throws HttpClientException
     */
    public static function forPut(Url $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Url $url The Url object
     * @param Body $body The Body object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: the http protocol version string
     * @throws HttpClientException
     */
    public static function forPatch(Url $url, Body $body, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: http protocol version string
     * @throws HttpClientException
     */
    public static function forDelete(Url $url, ?Headers $headers = null, string $version = '1.1'): self
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
     * @param Body $body
     * @param Headers|null $headers
     * @throws HttpClientException
     */
    public static function addHeaderFromBody(Body $body, ?Headers $headers): Headers
    {
        $headers = $headers ?? Headers::create();
        $headers->addHeader(Header::fromField(ContentType::fromString($body->getMimeType())));
        $headers->addHeader(Header::fromField(ContentLength::fromInt($body->getContentLength())));

        return $headers;
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
    public function getUrl(): UriInterfaceSubset
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        $target = $this->url->getPath();

        if ($target == '') {
            $target = '/';
        }

        if ($this->url->getQuery() != '') {
            $target .= '?' . $this->url->getQuery();
        }

        return $target;
    }
}
