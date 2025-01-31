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
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Stream\Stream;
use Override;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Abstract class to describe a http message.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 */
abstract class Message implements MessageInterface
{
    protected Headers $headers;

    /**
     * @param Headers|null $headers Optional: Headers collection or null
     * @param StreamInterface|null $body Optional: Body object or null
     * @param string $version Optional: Http protocol version string
     */
    protected function __construct(?Headers $headers = null, protected ?StreamInterface $body = null, protected string $version = '1.1')
    {
        $this->headers = $headers ?? Headers::create();
    }

    /**
     * Return the Header collection as an array.
     */
    #[Override]
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
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    #[Override]
    public function getBody(): StreamInterface
    {
        if (!$this->body instanceof StreamInterface) {
            return Stream::fromFileMode('r+');
        }

        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function hasHeader(string $name): bool
    {
        return $this->headers->has($name);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getHeader(string $name): array
    {
        try {
            return $this->headers->get($name)->getValues();
        } catch (InvalidArgumentException) {
            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getHeaderLine(string $name): string
    {
        try {
            return $this->headers->get($name)->getValue();
        } catch (InvalidArgumentException) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withHeader(string $name, $value): self
    {
        $cloned = clone $this;
        $cloned->assertHeader($name, $value);

        if (is_array($value)) {
            $cloned->headers->replace(Header::fromArray($name, $value));
        } else {
            $cloned->headers->replace(Header::fromString($name, $value));
        }

        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withProtocolVersion(string $version): self
    {
        $cloned = clone $this;
        $cloned->version = $version;

        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withAddedHeader(string $name, $value): self
    {
        $cloned = clone $this;
        $cloned->assertHeader($name, $value);

        if ($cloned->headers->has($name)) {
            if (is_array($value)) {
                $cloned->headers->get($name)->addValues($value);
            } else {
                $cloned->headers->get($name)->addValue($value);
            }
        } else {
            // Field does not exist, create new header
            $header = is_array($value) ? Header::fromArray($name, $value) : Header::fromString($name, $value);
            $cloned->headers->add($header);
        }

        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withoutHeader(string $name): MessageInterface
    {
        $cloned = clone $this;
        $cloned->headers->remove($name);

        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withBody(StreamInterface $body): MessageInterface
    {
        if (!$body->isReadable()) {
            throw new InvalidArgumentException('Body stream must be readable');
        }

        $cloned = clone $this;
        $cloned->body = $body;

        return $cloned;
    }

    /**
     * Checks the header data.
     *
     * @throws InvalidArgumentException
     */
    private function assertHeader(string $name, array | float | int | string $value): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Header must be a non empty string');
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                if (!is_string($val) && !is_numeric($val)) {
                    throw new InvalidArgumentException('Values must a string or numeric');
                }
            }
        }
    }
}
