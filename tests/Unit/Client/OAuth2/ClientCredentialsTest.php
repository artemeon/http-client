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

namespace Artemeon\HttpClient\Tests\Unit\Client\OAuth2;

use Artemeon\HttpClient\Client\Decorator\OAuth2\ClientCredentials;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClientCredentialsTest extends TestCase
{
    public function testCredentials(): void
    {
        $credentials = ClientCredentials::forHeaderAuthorization('foo', 'bar');

        self::assertSame('foo', $credentials->getClientId());
        self::assertSame('bar', $credentials->getClientSecret());

        self::assertSame(['grant_type' => 'client_credentials', 'client_id' => 'foo', 'client_secret' => 'bar'], $credentials->toArray());
        self::assertSame(['grant_type' => 'client_credentials'], $credentials->toArray(false));
    }
}
