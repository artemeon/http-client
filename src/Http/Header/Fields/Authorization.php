<?php

/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Header\Fields;

use Artemeon\HttpClient\Http\Header\HeaderField;

/**
 * Class to describe the header field 'Authorisation'.
 *
 * Example:
 * ```php
 * Authorisation::forAuthBearer('some-string-with-credentials')
 * ```
 */
class Authorization implements HeaderField
{
    /**
     * @param string $type The type of the http authorization
     * @param string $credentials The credentials string
     */
    private function __construct(private readonly string $type, private readonly string $credentials)
    {
    }

    /**
     * Name constructor to create an 'Authorisation: Bearer' field.
     *
     * @param string $credentials String with credentials for Bearer authorisation
     */
    public static function forAuthBearer(string $credentials): self
    {
        return new self('Bearer', $credentials);
    }

    /**
     * Name constructor to create an 'Authorisation: Basic' field.
     *
     * @param string $user String for the username
     * @param string $password String for the password
     */
    public static function forAuthBasic(string $user, string $password): self
    {
        return new self('Basic', base64_encode($user . ':' . $password));
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return self::AUTHORIZATION;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getValue(): string
    {
        return $this->type . ' ' . $this->credentials;
    }
}
