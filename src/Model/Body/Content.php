<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body;

class Content
{
    /** @var int */
    private $length;

    /** @var string */
    private $type;

    /** @var string */
    private $value;

    public function __construct(string $type, string $value)
    {
        $this->type  = $type;
        $this->value = $value;
        $this->length = strlen($value);
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return $this->value;
    }

    public static function forJsonEncoded(string $jsonEncoded): self
    {
        return new self(ContentTypes::TYPE_JSON, $jsonEncoded);
    }

    public static function forFormUrlEncoded(string $urlEncoded)
    {
        return new self(ContentTypes::TYPE_FORM_URL_ENCODED, $urlEncoded);
    }
}
