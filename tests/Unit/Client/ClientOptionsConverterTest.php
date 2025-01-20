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
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers \Artemeon\HttpClient\Client\Options\ClientOptionsConverter
 */
class ClientOptionsConverterTest extends TestCase
{
    use ProphecyTrait;

    private ClientOptionsConverter $clientOptionConverter;
    private ClientOptions $clientOptions;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setUp(): void
    {
        $this->clientOptions = ClientOptions::fromDefaults();
        $this->clientOptionConverter = new ClientOptionsConverter();
    }

    /**
     * @test
     */
    public function verifyKey_IsFalse(): void
    {
        $this->clientOptions->optDisableSslVerification();
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertFalse($options[GuzzleRequestOptions::VERIFY]);
    }

    /**
     * @test
     */
    public function verifyKey_IsTrue(): void
    {
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertTrue($options[GuzzleRequestOptions::VERIFY]);
    }

    /**
     * @test
     */
    public function verifyKey_IsCaBundlePathString(): void
    {
        $expected = '/path/ca/bundle';
        $this->clientOptions->optSetCustomCaBundlePath($expected);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertSame($expected, $options[GuzzleRequestOptions::VERIFY]);
    }

    /**
     * @test
     */
    public function allowRedirectsKey_ReturnFalse(): void
    {
        $this->clientOptions->optDisableRedirects();
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertFalse($options[GuzzleRequestOptions::ALLOW_REDIRECTS]);
    }

    /**
     * @test
     */
    public function allowRedirectsKey_ReturnsValidArray(): void
    {
        $expectedMax = 10;
        $expectedReferer = true;

        $this->clientOptions->optSetMaxRedirects($expectedMax);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertIsArray($options[GuzzleRequestOptions::ALLOW_REDIRECTS]);
        self::assertSame($expectedReferer, $options[GuzzleRequestOptions::ALLOW_REDIRECTS]['referer']);
        self::assertSame($expectedMax, $options[GuzzleRequestOptions::ALLOW_REDIRECTS]['max']);
    }

    /**
     * @test
     */
    public function timeoutKey_HasExpectedIntValue(): void
    {
        $expected = 22;
        $this->clientOptions->optSetTimeout($expected);
        $options = $this->clientOptionConverter->toGuzzleOptionsArray($this->clientOptions);

        self::assertSame($expected, $options[GuzzleRequestOptions::TIMEOUT]);
    }
}
