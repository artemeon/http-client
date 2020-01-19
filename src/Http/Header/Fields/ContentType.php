<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Header\Fields;

use Artemeon\HttpClient\Http\Header\HeaderField;

/**
 * Class to describe the header field 'Content-Type'
 */
class ContentType implements HeaderField
{
    /** @var string */
    private $mimeType;

    /**
     * ContentType constructor.
     */
    private function __construct($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Named constructor to create an instance from the given string value
     *
     * @param string $mimeType MIME type string @see \Artemeon\HttpClient\Http\MediaType
     */
    public static function fromString(string $mimeType): self
    {
        return new self($mimeType);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HeaderField::CONTENT_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->mimeType;
    }
}
