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
 * Interface to realize several strategy's to store AccessToken.
 */
interface AccessTokenCache
{
    /**
     * Add token to the cache.
     */
    public function add(AccessToken $accessToken);

    /**
     * Get token from the cache.
     *
     * @throws RuntimeException
     */
    public function get(): AccessToken;

    /**
     * Check if the token is expired or not set.
     */
    public function isExpired(): bool;
}
