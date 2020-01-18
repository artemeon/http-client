<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body;

use Artemeon\HttpClient\Model\Body\Encoder\Encoder;
use Artemeon\HttpClient\Model\Body\Reader\Reader;

/**
 * Value object zo cover all http body related content
 */
class Body
{
    /** @var int */
    private $length;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $value;

    /**
     * Body constructor.
     */
    private function __construct(string $mimeType, string $value)
    {
        $this->mimeType = $mimeType;
        $this->value = $value;
        $this->length = strlen($value);
    }

    /**
     * Named constructor to create an instance based on the given values
     */
    public static function fromString(string $mimeType, string $value): self
    {
        return new self($mimeType, $value);
    }

    /**
     * Named constructor to create an instance based on the given Encoder
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
        $value = $reader->read();

        return new self($mimeType, $value);
    }

    /**
     * Returns the calculated content length
     */
    public function getContentLength(): int
    {
        return $this->length;
    }

    /**
     * Returns the associated MIME type string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Returns the content string
     */
    public function getContent(): string
    {
        return $this->value;
    }
}
