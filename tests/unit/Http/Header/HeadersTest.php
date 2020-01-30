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

namespace Artemeon\HttpClient\Tests\Http\Header;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Header\Fields\Authorization;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Artemeon\HttpClient\Http\Header\Headers
 */
class HeadersTest extends TestCase
{
    /** @var Headers */
    private $headers;

    public function setUp(): void
    {
        $this->headers = Headers::create();
    }

    /**
     * @test
     */
    public function hasHeader_IsCaseIncentive_ReturnsTrue(): void
    {
        $this->headers->addHeader(Header::fromField(UserAgent::fromString()));
        self::assertTrue($this->headers->hasHeader('USER-AGENT'));
    }

    /**
     * @test
     */
    public function hasHeader_NotExists_ReturnsFalse(): void
    {
        $this->headers->addHeader(Header::fromField(UserAgent::fromString()));
        self::assertFalse($this->headers->hasHeader('not-exists'));
    }

    /**
     * @test
     */
    public function fomFields_CreatesValidHeaders(): void
    {
        $this->headers = Headers::fromFields([UserAgent::fromString('test')]);
        $userAgent = $this->headers->getHeader(HeaderField::USER_AGENT);

        self::assertCount(1, $this->headers);
        self::assertSame(HeaderField::USER_AGENT, $userAgent->getFieldName());
        self::assertSame('test', $userAgent->getValue());
    }

    /**
     * @test
     */
    public function getHeader_NotExists_ThrowsException(): void
    {
        $this->expectException(HttpClientException::class);
        $this->headers->getHeader('not-exists');
    }

    /**
     * @test
     */
    public function getHeader_Exists_ReturnsValue(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->addHeader($expected);

        self::assertSame($expected, $this->headers->getHeader(HeaderField::AUTHORIZATION));
    }

    /**
     * @test
     */
    public function addHeader_Exists_ThrowsException(): void
    {
        $header = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));

        $this->expectException(HttpClientException::class);
        $this->headers->addHeader($header);
        $this->headers->addHeader($header);
    }

    /**
     * @test
     */
    public function getIterator_ReturnsArrayIterator(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->addHeader($expected);

        self::assertCount(1, $this->headers->getIterator());
    }
}
