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

use http\Exception\RuntimeException;

/**
 * Class to store AccessToken in memory
 */
class InMemoryAccessTokenCache implements AccessTokenCache
{
    private ?AccessToken $token = null;
    private int $expireTime;

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
        if ($this->isExpired()) {
            throw new RuntimeException('Token expired or not set');
        }

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
