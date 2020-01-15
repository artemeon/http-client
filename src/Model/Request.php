<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

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
    private $headerBag;

    /** @var Body */
    private $content;

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

    private function __construct(string $method, Url $url, Headers $headerBag = null, Body $content = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->headerBag = $headerBag ?? new Headers();
        $this->content = $content;

        if ($content instanceof Body) {
            $this->headerBag->addHeader(Header::fromString(HeaderFields::CONTENT_TYPE, $content->getMimeType()));
            $this->headerBag->addHeader(
                Header::fromString(HeaderFields::CONTENT_LENGTH, strval($content->getContentLength()))
            );
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getHeaderBag(): Headers
    {
        return $this->headerBag;
    }

    public function getContent(): Body
    {
        return $this->content;
    }

    public static function forGet(Url $url, Headers $headerBag = null): self
    {
        return new self(
            self::METHOD_GET,
            $url,
            $headerBag
        );
    }

    public static function forPost(Url $url, Body $content, Headers $headerBag = null): self
    {
        return new self(
            self::METHOD_POST,
            $url,
            $headerBag,
            $content
        );
    }
}
