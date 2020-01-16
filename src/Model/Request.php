<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\Header\Header;
use Artemeon\HttpClient\Model\Header\HeaderFields;
use Artemeon\HttpClient\Model\Header\Headers;

use function strval;

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
            $this->headers->addHeader(Header::fromString(HeaderFields::CONTENT_TYPE, $body->getMimeType()));
            $this->headers->addHeader(
                Header::fromString(HeaderFields::CONTENT_LENGTH, strval($body->getContentLength()))
            );
        }

        $this->version = $version;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    public function getBody(): ?Body
    {
        return $this->body;
    }

    public function hasBody(): bool
    {
        return $this->body instanceof Body;
    }

    /**
     * @return float
     */
    public function getVersion(): float
    {
        return $this->version;
    }

    /**
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
}
