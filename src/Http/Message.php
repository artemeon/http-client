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
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Psr7\MessageInterfaceSubset;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;

use function is_array;

/**
 * Abstract class to describe a http message
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 */
abstract class Message implements MessageInterfaceSubset
{
    /** @var Headers */
    protected $headers;

    /** @var StreamInterface */
    protected $body;

    /** @var string */
    protected $version;

    /**
     * Message constructor.
     *
     * @param Headers|null $headers Optional: Headers collection or null
     * @param StreamInterface|null $body Optional: Body object or null
     * @param string $version Optional: Http protocol version string
     */
    protected function __construct(?Headers $headers = null, StreamInterface $body = null, string $version = '1.1')
    {
        $this->headers = $headers ?? Headers::create();
        $this->body = $body;
        $this->version = $version;
    }

    /**
     * Return the Header collection
     */
    public function getHeaders(): array
    {
        $headers = [];

        /** @var Header $header */
        foreach ($this->headers as $header) {
            $headers[$header->getFieldName()] = $header->getValues();
        }

        return $headers;
    }

    /**
     * @inheritDoc
     * @throws HttpClientException
     */
    public function getBody(): StreamInterface
    {
        if (!$this->body instanceof StreamInterface) {
            return Stream::fromString('');
        }

        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return $this->headers->hasHeader(strval($name));
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        try {
            return $this->headers->getHeader(strval($name))->getValues();
        } catch (HttpClientException $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        try {
            return $this->headers->getHeader(strval($name))->getValue();
        } catch (HttpClientException $e) {
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): self
    {
        $cloned = clone $this;

        if (is_array($value)) {
            $cloned->headers->replaceHeader(Header::fromArray($name, $value));
        } else {
            $cloned->headers->replaceHeader(Header::fromString($name, $value));
        }

        return $cloned;
    }
}
