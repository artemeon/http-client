<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

use Artemeon\HttpClient\Model\Authorisation;

use function strval;

class Header
{
    private $name;
    private $values;

    private function __construct(string $name, string $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValues(): string
    {
        return $this->values;
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
