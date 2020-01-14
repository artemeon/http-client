<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

use function base64_encode;

class Authorisation
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

    public function __toString(): string
    {
        return $this->type . ': ' . $this->credentials;
    }
}