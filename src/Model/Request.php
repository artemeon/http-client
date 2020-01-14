<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

use Artemeon\HttpClient\Model\Body\Content;
use Artemeon\HttpClient\Model\Header\Header;
use Artemeon\HttpClient\Model\Header\HeaderBag;
use Artemeon\HttpClient\Model\Header\HeaderFields;

use function strval;

class Request
{
    /** @var string */
    private $method;

    /** @var string */
    private $url;

    /** @var HeaderBag */
    private $headerBag;

    /** @var Content */
    private $content;

    /** @var string */
    const METHOD_POST = 'POST';

    /** @var string */
    const METHOD_GET = 'GET';

    /** @var string */
    const METHOD_PUT = 'PUT';

    /** @var string */
    const METHOD_DELETE = 'DELETE';

    /** @var string */
    const METHOD_OPTIONS = 'OPTIONS';

    /** @var string */
    const METHOD_PATCH = 'PATCH';

    private function __construct(string $method, string $url, HeaderBag $headerBag = null, Content $content = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->headerBag = $headerBag ?? new HeaderBag();
        $this->content = $content;

        if ($content instanceof Content) {
            $this->headerBag->addHeader(Header::fromString(HeaderFields::CONTENT_TYPE, $content->getType()));
            $this->headerBag->addHeader(Header::fromString(HeaderFields::CONTENT_LENGTH, strval($content->getLength())));
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHeaderBag(): HeaderBag
    {
        return $this->headerBag;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public static function forGet(string $url, HeaderBag $headerBag = null): self
    {
        return new self(
            self::METHOD_GET,
            $url,
            $headerBag,
        );
    }

    public static function forPost(string $url, Content $content, HeaderBag $headerBag = null): self
    {
        return new self(
            self::METHOD_POST,
            $url,
            $headerBag,
            $content
        );
    }
}
