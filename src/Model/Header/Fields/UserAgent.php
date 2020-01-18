<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header\Fields;

use Artemeon\HttpClient\Model\Header\HeaderField;

class UserAgent implements HeaderField
{
    /** @var string */
    private $userAgent;

    /**
     * UserAgent constructor.
     */
    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public static function fromString(string $userAgent = "ArtemeonHttpClient"): self
    {
        return new self($userAgent);
    }

    public function getName(): string
    {
        return HeaderField::USER_AGENT;
    }

    public function getValue(): string
    {
        return $this->userAgent;
    }
}
