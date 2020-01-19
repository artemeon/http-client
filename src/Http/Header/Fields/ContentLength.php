<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Header\Fields;

use Artemeon\HttpClient\Http\Header\HeaderField;

/**
 * Class to describe the header field 'Content-Length'
 */
class ContentLength implements HeaderField
{
    /** @var int */
    private $contentLength;

    /**
     * ContentLength constructor.
     */
    public function __construct(int $contentLength)
    {
        $this->contentLength = $contentLength;
    }

    /**
     * Named constructor to create an instance from the given int value
     */
    public static function fromInt(int $contentLength): self
    {
        return new self($contentLength);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HeaderField::CONTENT_LENGTH;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return strval($this->contentLength);
    }
}
