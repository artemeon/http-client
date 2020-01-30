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

namespace Artemeon\HttpClient\Client\Decorator\OAuth2;

use Artemeon\HttpClient\Exception\HttpClientException;

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
     * @param string $token
     * @param string $expires
     * @param string $type
     * @param string $scope
     * @throws HttpClientException
     */
    private function __construct(string $token, int $expires, string $type, string $scope = '')
    {
        if (empty($token) || empty($expires) || empty($type)) {
            throw new HttpClientException(
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
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}