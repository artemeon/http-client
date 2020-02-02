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
use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Abstract class to describe a http message
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 */
abstract class Message implements MessageInterface
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
     */
    public function getBody(): StreamInterface
    {
        if (!$this->body instanceof StreamInterface) {
            return Stream::fromFileMode('r+');
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
        return $this->headers->has(strval($name));
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        try {
            return $this->headers->get(strval($name))->getValues();
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        try {
            return $this->headers->get(strval($name))->getValue();
        } catch (InvalidArgumentException $e) {
            return '';
        }
    }

    /**
     * @inheritDoc
     * @throws HttpClientException
     */
    public function withHeader($name, $value): self
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('name must be string value');
        }

        $cloned = clone $this;

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = trim($val);

                if (!is_string($value)) {
                    throw new InvalidArgumentException('value must be an array of strings');
                }
            }

            $cloned->headers->replace(Header::fromArray($name, $value));
        } else {
            if (!is_string($value)) {
                throw new InvalidArgumentException('value must be an string');
            }

            $cloned->headers->replace(Header::fromString($name, $value));
        }

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): self
    {
        $cloned = clone $this;
        $cloned->version = strval($version);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): self
    {
        $cloned = clone $this;

        if ($cloned->headers->has($name)) {
            $cloned->headers->get($name)->addValue($value);
        } else {
            // Field does not exists, create new header
            $header = is_array($value) ? Header::fromArray($name, $value) : Header::fromString($name, $value);
            $cloned->headers->add($header);
        }

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $cloned = clone $this;
        $cloned->headers->remove($name);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        if (!$body->isReadable()) {
            throw new InvalidArgumentException('Body stream must be readable');
        }

        $cloned = clone $this;
        $cloned->body = $body;

        return $cloned;
    }
}
