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

namespace Artemeon\HttpClient\Client\Decorator\OAuth2\Token;

use Artemeon\HttpClient\Exception\RuntimeException;

/**
 * Class to describe a OAuth2 access token
 */
class AccessToken
{
    /** @var string */
    private $token;

    /** @var int */
    private $expires;

    /** @var string */
    private $type;

    /** @var string */
    private $scope;

    /**
     * AccessToken constructor.
     *
     * @param string $token The OAuth2 access token
     * @param int $expires The expires in integer
     * @param string $type The type of authorization
     * @param string $scope The scope of the authorization
     */
    private function __construct(string $token, int $expires, string $type, string $scope = '')
    {
        if (empty($token) || empty($expires) || empty($type)) {
            throw new RuntimeException(
                "Access token fields: 'access_token', 'expires_in', 'token_type' are mandatory"
            );
        }

        $this->token = $token;
        $this->expires = $expires;
        $this->type = $type;
        $this->scope = $scope;
    }

    /**
     * Named constructor to create an instance based on the given json encoded body string
     *
     * @param string $json Json encoded response string
     */
    public static function fromJsonString(string $json): self
    {
        $data = json_decode($json, true);

        return new self(
            isset($data['access_token']) ? (string) $data['access_token'] : '',
            isset($data['expires_in']) ? (int) $data['expires_in'] : 0,
            isset($data['token_type']) ? (string) $data['token_type'] : '',
            isset($data['scope']) ? (string) $data['scope'] : ''
        );
    }

    /**
     * Returns the access token string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns the expires in integer value
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * Return the type of the authorization
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return the scope of the authorization
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
