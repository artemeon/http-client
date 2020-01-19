<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Header\Fields;

use Artemeon\HttpClient\Http\Header\HeaderField;

/**
 * Class to describe the header field 'Authorisation'
 */
class Authorisation implements HeaderField
{
    /** @var string */
    private $type;

    /** @var string */
    private $credentials;

    /**
     * Authorisation constructor.
     */
    private function __construct(string $type, string $credentials)
    {
        $this->type = $type;
        $this->credentials = $credentials;
    }

    /**
     * Name constructor to create an 'Authorisation: Bearer' field
     */
    public static function forAuthBearer(string $credentials): self
    {
        return new self('Bearer', $credentials);
    }

    /**
     * Name constructor to create an 'Authorisation: Basic' field
     */
    public static function forAuthBasic(string $user, string $password): self
    {
        return new self('Basic', base64_encode($user . ':' . $password));
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::AUTHORISATION;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->type . ': ' . $this->credentials;
    }
}
