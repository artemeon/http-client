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

namespace Artemeon\HttpClient\Tests\Http;

use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Uri;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * @test
     */
    public function fromString_SetValidValues(): void
    {
        $expected = 'http://www.artemeon.de';
        $url = Uri::fromString($expected);
        self::assertSame($expected, $url->__toString());
    }

    /**
     * @test
     */
    public function fromString_UrlIsInvalid_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Uri::fromString('dfg;docs//invalid');
    }

    /**
     * @test
     */
    public function getQuery_ReturnExpectedValue(): void
    {
        $expected = 'user=john.doe';
        $url = Uri::fromQueryParams('http://www.artemeon.de', ['user' => 'john.doe']);
        self::assertSame($expected, $url->getQuery());
    }

    /**
     * @test
     */
    public function getFragment_ReturnExpectedValue(): void
    {
        $expected = 'anker';
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html#' . $expected);
        self::assertSame($expected, $url->getFragment());
    }

    /**
     * @test
     */
    public function getFragment_ReturnsEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html');
        self::assertSame('', $url->getFragment());
    }

    /**
     * @test
     */
    public function getUserInfo_ReturnUserPassword(): void
    {
        $url = Uri::fromString('https://dsi:topsecret@www.artemeon.de');
        self::assertSame('dsi:topsecret', $url->getUserInfo());
    }

    /**
     * @test
     */
    public function getUserInfo_ReturnOnlyUser(): void
    {
        $url = Uri::fromString('https://dsi@www.artemeon.de');
        self::assertSame('dsi', $url->getUserInfo());
    }

    /**
     * @test
     */
    public function getUserInfo_ReturnEmptyString(): void
    {
        $url = Uri::fromString('https://www.artemeon.de');
        self::assertSame('', $url->getUserInfo());
    }

    /**
     * @test
     */
    public function getScheme_ReturnExpectedValue(): void
    {
        $url = Uri::fromString('ftp://dsi:topsecret@www.artemeon.de');
        self::assertSame('ftp', $url->getScheme());
    }

    /**
     * @test
     */
    public function getHost_ReturnExpectedValue(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('www.artemeon.de', $url->getHost());
    }

    /**
     * @test
     */
    public function getPort_ReturnExpectedNull(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/path/to/file.html');
        self::assertNull($url->getPort());
    }

    /**
     * @test
     */
    public function getPort_ReturnExpectedInt(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame(8080, $url->getPort());
    }

    /**
     * @test
     */
    public function getPath_ReturnExpectedString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('/path/to/file.html', $url->getPath());
    }

    /**
     * @test
     */
    public function getPath_ReturnExpectedEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080');
        self::assertSame('', $url->getPath());
    }
}
