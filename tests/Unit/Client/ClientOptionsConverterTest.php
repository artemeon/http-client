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
use Artemeon\HttpClient\Client\Options\ClientOptionsConverter;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClientOptionsConverterTest extends TestCase
{
    private ClientOptionsConverter $clientOptionConverter;
    private ClientOptions $clientOptions;

    /**
     * @inheritDoc
     */
    #[Override]
    public function setUp(): void
    {
        $this->clientOptions = ClientOptions::fromDefaults();
        $this->clientOptionConverter = new ClientOptionsConverter();
    }

    public function testVerifyKeyIsFalse(): void
    {
        $this->clientOptions->optDisableSslVerification();
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertFalse($options[GuzzleRequestOptions::VERIFY]);
    }

    public function testVerifyKeyIsTrue(): void
    {
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertTrue($options[GuzzleRequestOptions::VERIFY]);
    }

    public function testVerifyKeyIsCaBundlePathString(): void
    {
        $expected = '/path/ca/bundle';
        $this->clientOptions->optSetCustomCaBundlePath($expected);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertSame($expected, $options[GuzzleRequestOptions::VERIFY]);
    }

    public function testAllowRedirectsKeyReturnFalse(): void
    {
        $this->clientOptions->optDisableRedirects();
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertFalse($options[GuzzleRequestOptions::ALLOW_REDIRECTS]);
    }

    public function testAllowRedirectsKeyReturnsValidArray(): void
    {
        $expectedMax = 10;
        $expectedReferer = true;

        $this->clientOptions->optSetMaxRedirects($expectedMax);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertIsArray($options[GuzzleRequestOptions::ALLOW_REDIRECTS]);
        self::assertSame($expectedReferer, $options[GuzzleRequestOptions::ALLOW_REDIRECTS]['referer']);
        self::assertSame($expectedMax, $options[GuzzleRequestOptions::ALLOW_REDIRECTS]['max']);
    }

    public function testTimeoutKeyHasExpectedIntValue(): void
    {
        $expected = 22;
        $this->clientOptions->optSetTimeout($expected);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertSame($expected, $options[GuzzleRequestOptions::TIMEOUT]);
    }
}
