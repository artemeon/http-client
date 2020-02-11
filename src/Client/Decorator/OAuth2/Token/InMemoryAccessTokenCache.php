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

/**
 * Class to store AccessToken in memory
 */
class InMemoryAccessTokenCache implements AccessTokenCache
{
    /** @var AccessToken */
    private $token;

    /** @var int */
    private $expireTime;

    /**
     * @inheritDoc
     */
    public function add(AccessToken $accessToken)
    {
        $this->token = $accessToken;
        $this->expireTime = time() + $accessToken->getExpires();
    }

    /**
     * @inheritDoc
     */
    public function get(): AccessToken
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function isExpired(): bool
    {
        if (!$this->token instanceof AccessToken) {
            return true;
        }

        if (time() >= $this->expireTime) {
            return true;
        }

        return false;
    }
}