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
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers \Artemeon\HttpClient\Client\Options\ClientOptions
 */
class ClientOptionsTest extends TestCase
{
    use ProphecyTrait;

    private ClientOptions $clientOptions;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->clientOptions = ClientOptions::fromDefaults();
    }

    /**
     * @test
     */
    public function fromDefaults_setValidValues(): void
    {
        self::assertSame(true, $this->clientOptions->isRedirectAllowed());
        self::assertSame(10, $this->clientOptions->getTimeout());
        self::assertSame(true, $this->clientOptions->isSslVerificationEnabled());
        self::assertSame('', $this->clientOptions->getCustomCaBundlePath());
        self::assertSame(5, $this->clientOptions->getMaxAllowedRedirects());
        self::assertSame(true, $this->clientOptions->isRefererForRedirectsEnabled());
        self::assertSame(false, $this->clientOptions->hasCustomCaBundlePath());
    }

    /**
     * @test
     */
    public function ChangedOptions_SetValidValues(): void
    {
        $this->clientOptions->optDisableRedirects();
        $this->clientOptions->optSetTimeout(50);
        $this->clientOptions->optDisableSslVerification();
        $this->clientOptions->optSetCustomCaBundlePath('/custom/path');
        $this->clientOptions->optSetMaxRedirects(10);
        $this->clientOptions->optDisableRefererForRedirects();

        self::assertSame(false, $this->clientOptions->isRedirectAllowed());
        self::assertSame(50, $this->clientOptions->getTimeout());
        self::assertSame(false, $this->clientOptions->isSslVerificationEnabled());
        self::assertSame('/custom/path', $this->clientOptions->getCustomCaBundlePath());
        self::assertSame(true, $this->clientOptions->hasCustomCaBundlePath());
        self::assertSame(10, $this->clientOptions->getMaxAllowedRedirects());
        self::assertSame(false, $this->clientOptions->isRefererForRedirectsEnabled());
    }
}
