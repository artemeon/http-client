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

/**
 * Abstract class to describe a http message
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 */
abstract class Message
{
    /** @var Headers */
    protected $headers;

    /** @var Body */
    protected $body;

    /** @var float */
    protected $version;

    /**
     * Message constructor.
     *
     * @param Headers|null $headers Optional: Headers collection or null
     * @param Body|null $body Optional: Body object or null
     * @param float $version Optional: Http protocol version string
     *
     * @throws HttpClientException
     */
    protected function __construct(?Headers $headers = null, ?Body $body = null, float $version = 1.1)
    {
        $this->headers = $headers ?? new Headers();
        $this->body = $body;

        if ($body instanceof Body) {
            $this->headers->addHeader(Header::fromField(ContentType::fromString($body->getMimeType())));
            $this->headers->addHeader(Header::fromField(ContentLength::fromInt($body->getContentLength())));
        }

        $this->version = $version;
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
    public function getBody(): string
    {
        if (!$this->hasBody()) {
            return '';
        }

        return $this->body->getContent();
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