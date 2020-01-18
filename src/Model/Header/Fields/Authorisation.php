<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header\Fields;

use Artemeon\HttpClient\Model\Header\HeaderField;

class Authorisation implements HeaderField
{
    /** @var string */
    private $type;

    /** @var string */
    private $credentials;

    private function __construct(string $type, string $credentials)
    {
        $this->type =  $type;
        $this->credentials = $credentials;
    }

    public static function forAuthBearer(string $credentials): self
    {
        return new self('Bearer', $credentials);
    }

    public static function forAuthBasic(string $user, string $password): self
    {
        return new self('Basic', base64_encode($user . ':' . $password));
    }

    public function getName(): string
    {
        return self::AUTHORISATION;
    }

    public function getValue(): string
    {
        return $this->type . ': ' . $this->credentials;
    }
}
