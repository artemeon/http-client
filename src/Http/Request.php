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

class Request
{
    /** @var string */
    private $method;

    /** @var Url */
    private $url;

    /** @var Headers */
    private $headers;

    /** @var Body */
    private $body;

    /** @var float */
    private $version;

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
     * @param string $method The request method string
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param Body|null $body Optional: Body object or null
     * @param float $version Optional: Http protocol version string
     *
     * @throws HttpClientException
     */
    private function __construct(
        string $method,
        Url $url,
        Headers $headers = null,
        ?Body $body = null,
        float $version = 1.1
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers ?? new Headers();
        $this->body = $body;

        if ($body instanceof Body) {
            $this->headers->addHeader(Header::fromField(ContentType::fromString($body->getMimeType())));
            $this->headers->addHeader(Header::fromField(ContentLength::fromInt($body->getContentLength())));
        }

        $this->version = $version;
    }

    /**
     * Named constructor to create an instance for post requests
     *
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: http protocol version string
     *
     * @throws HttpClientException
     */
    public static function forGet(Url $url, ?Headers $headers = null, float $version = 1.1): self
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
     *
     * @throws HttpClientException
     */
    public static function forOptions(Url $url, ?Headers $headers = null, float $version = 1.1): self
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
     *
     * @throws HttpClientException
     */
    public static function forPost(Url $url, Body $body, ?Headers $headers = null, float $version = 1.1): self
    {
        return new self(
            self::METHOD_POST,
            $url,
            $headers,
            $body,
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
     *
     * @throws HttpClientException
     */
    public static function forPut(Url $url, Body $body, ?Headers $headers = null, float $version = 1.1): self
    {
        return new self(
            self::METHOD_PUT,
            $url,
            $headers,
            $body,
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
     *
     * @throws HttpClientException
     */
    public static function forPatch(Url $url, Body $body, ?Headers $headers = null, float $version = 1.1): self
    {
        return new self(
            self::METHOD_PATCH,
            $url,
            $headers,
            $body,
            $version
        );
    }

    /**
     * Named constructor to create an instance for DELETE requests
     *
     * @param Url $url The Url object
     * @param Headers|null $headers Optional: Headers collection or null
     * @param float $version Optional: http protocol version string
     *
     * @throws HttpClientException
     */
    public static function forDelete(Url $url, ?Headers $headers = null, float $version = 1.1): self
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
     * Returns the request method string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the Url Object
     */
    public function getUrl(): Url
    {
        return $this->url;
    }

    /**
     * Return the Header collection
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * Returns the body or null
     */
    public function getBody(): ?Body
    {
        return $this->body;
    }

    /**
     * Checks if the request contains a body
     */
    public function hasBody(): bool
    {
        return $this->body instanceof Body;
    }

    /**
     * Returns the http protocol version float number
     */
    public function getVersion(): float
    {
        return $this->version;
    }
}
