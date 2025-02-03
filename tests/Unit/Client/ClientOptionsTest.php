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

namespace Artemeon\HttpClient\Tests\Unit\Client;

use Artemeon\HttpClient\Client\Options\ClientOptions;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClientOptionsTest extends TestCase
{
    private ClientOptions $clientOptions;

    /**
     * @inheritDoc
     */
    #[Override]
    public function setUp(): void
    {
        $this->clientOptions = ClientOptions::fromDefaults();
    }

    public function testFromDefaultsSetValidValues(): void
    {
        self::assertTrue($this->clientOptions->isRedirectAllowed());
        self::assertSame(10, $this->clientOptions->getTimeout());
        self::assertTrue($this->clientOptions->isSslVerificationEnabled());
        self::assertSame('', $this->clientOptions->getCustomCaBundlePath());
        self::assertSame(5, $this->clientOptions->getMaxAllowedRedirects());
        self::assertTrue($this->clientOptions->isRefererForRedirectsEnabled());
        self::assertFalse($this->clientOptions->hasCustomCaBundlePath());
    }

    public function testChangedOptionsSetValidValues(): void
    {
        $this->clientOptions->optDisableRedirects();
        $this->clientOptions->optSetTimeout(50);
        $this->clientOptions->optDisableSslVerification();
        $this->clientOptions->optSetCustomCaBundlePath('/custom/path');
        $this->clientOptions->optSetMaxRedirects(10);
        $this->clientOptions->optDisableRefererForRedirects();

        self::assertFalse($this->clientOptions->isRedirectAllowed());
        self::assertSame(50, $this->clientOptions->getTimeout());
        self::assertFalse($this->clientOptions->isSslVerificationEnabled());
        self::assertSame('/custom/path', $this->clientOptions->getCustomCaBundlePath());
        self::assertTrue($this->clientOptions->hasCustomCaBundlePath());
        self::assertSame(10, $this->clientOptions->getMaxAllowedRedirects());
        self::assertFalse($this->clientOptions->isRefererForRedirectsEnabled());
    }
}
