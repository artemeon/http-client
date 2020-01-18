<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header\Fields;

use Artemeon\HttpClient\Model\Header\HeaderField;

class ContentType implements HeaderField
{
    /** @var string */
    private $contentType;

    /**
     * ContentType constructor.
     */
    public function __construct($contentType)
    {
        $this->contentType = $contentType;
    }

    public static function fromString(string $contentType): self
    {
        return new self($contentType);
    }

    public function getName(): string
    {
        return HeaderField::CONTENT_TYPE;
    }

    public function getValue(): string
    {
        return $this->contentType;
    }
}
