<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

use Artemeon\HttpClient\Model\Authorisation;

use function strval;

/**
 * Value object for a http header field
 */
class Header
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /**
     * Header constructor.
     */
    private function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Return the field name like "Accept-Encoding2
     */
    public function getFieldName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromString(string $name, string $value): self
    {
        return new self($name, $value);
    }

    public static function forAuthorisation(Authorisation $authorisation): self
    {
        return new self(HeaderFields::AUTHORISATION, strval($authorisation));
    }

    public static function forUserAgent(string $userAgent = "ArtemeonHttpClient")
    {
        return new self(HeaderFields::USER_AGENT, $userAgent);
    }
}
