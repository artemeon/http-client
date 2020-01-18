<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header\Fields;

use Artemeon\HttpClient\Model\Header\HeaderField;

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

    public static function fromInt(int $contentLength): self
    {
        return new self($contentLength);
    }

    public function getName(): string
    {
        return HeaderField::CONTENT_LENGTH;
    }

    public function getValue(): string
    {
        return strval($this->contentLength);
    }
}
