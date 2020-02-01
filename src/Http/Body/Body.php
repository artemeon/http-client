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

namespace Artemeon\HttpClient\Http\Body;

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Body\Encoder\Encoder;
use Artemeon\HttpClient\Http\Body\Reader\Reader;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Value object to cover all http body related content
 */
class Body
{
    /** @var int */
    private $length;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $stream;

    /**
     * Body constructor.
     */
    private function __construct(string $mimeType, StreamInterface $stream)
    {
        $this->mimeType = $mimeType;
        $this->stream = $stream;
        $this->length = $stream->getSize();
    }

    /**
     * Named constructor to create an instance based on the given values
     *
     * @param string $mimeType MIME-Type of the content
     * @param string $value String to set the content
     * @throws RuntimeException
     */
    public static function fromString(string $mimeType, string $value): self
    {
        return new self($mimeType, Stream::fromString($value));
    }

    /**
     * Named constructor to create an instance based on the given Encoder
     *
     * @param Encoder $encoder Body Encoder implementation
     * @throws RuntimeException
     */
    public static function fromEncoder(Encoder $encoder): self
    {
        return new self($encoder->getMimeType(), $encoder->encode());
    }

    /**
     * Named constructor to create an instance based on the given Reader
     */
    public static function fromReader(Reader $reader): self
    {
        $mimeType = MediaType::mapFileExtensionToMimeType($reader->getFileExtension());
        $stream = $reader->getStream();

        return new self($mimeType, $stream);
    }

    /**
     * Returns the calculated content length
     */
    public function getContentLength(): int
    {
        return $this->length;
    }

    /**
     * Returns the associated mime type string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Returns the content string
     */
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}
