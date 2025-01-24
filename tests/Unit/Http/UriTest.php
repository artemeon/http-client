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

namespace Artemeon\HttpClient\Tests\Unit\Http;

use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Uri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UriTest extends TestCase
{
    #[Test]
    public function testFromStringSetValidValues(): void
    {
        $expected = 'http://www.artemeon.de';
        $url = Uri::fromString($expected);
        self::assertSame($expected, $url->__toString());
    }

    #[Test]
    public function testGetQueryReturnExpectedValue(): void
    {
        $expected = 'user=john.doe';
        $url = Uri::fromQueryParams('http://www.artemeon.de', ['user' => 'john.doe']);
        self::assertSame($expected, $url->getQuery());
    }

    #[Test]
    public function testGetFragmentReturnExpectedValue(): void
    {
        $expected = 'anker';
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html#' . $expected);
        self::assertSame($expected, $url->getFragment());
    }

    #[Test]
    public function testGetFragmentReturnsEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html');
        self::assertSame('', $url->getFragment());
    }

    #[Test]
    public function testGetUserInfoReturnUserPassword(): void
    {
        $url = Uri::fromString('https://dsi:topsecret@www.artemeon.de');
        self::assertSame('dsi:topsecret', $url->getUserInfo());
    }

    #[Test]
    public function testGetUserInfoReturnOnlyUser(): void
    {
        $url = Uri::fromString('https://dsi@www.artemeon.de');
        self::assertSame('dsi', $url->getUserInfo());
    }

    #[Test]
    public function testGetUserInfoReturnEmptyString(): void
    {
        $url = Uri::fromString('https://www.artemeon.de');
        self::assertSame('', $url->getUserInfo());
    }

    #[Test]
    public function testGetSchemeReturnExpectedValue(): void
    {
        $url = Uri::fromString('ftp://dsi:topsecret@www.artemeon.de');
        self::assertSame('ftp', $url->getScheme());
    }

    #[Test]
    public function testGetHostReturnExpectedValue(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('www.artemeon.de', $url->getHost());
    }

    #[Test]
    public function testGetPortReturnExpectedNull(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/path/to/file.html');
        self::assertNull($url->getPort());
    }

    #[Test]
    public function testGetPortReturnExpectedInt(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame(8080, $url->getPort());
    }

    #[Test]
    public function testGetPathReturnExpectedString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('/path/to/file.html', $url->getPath());
    }

    #[Test]
    public function testGetPathReturnExpectedEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080');
        self::assertSame('', $url->getPath());
    }

    #[Test]
    public function testWithSchemeIsNotStringThroesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $url = Uri::fromString('http://www.artemeon.de:8080');
        $url->withScheme(0);
    }

    #[Test]
    public function testWithSchemeReturnsUpdatedInstance(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080');
        $cloned = $url->withScheme('FTP');

        self::assertNotSame($url, $cloned);
        self::assertSame('ftp', $cloned->getScheme());
        self::assertSame('ftp://www.artemeon.de:8080', $cloned->__toString());
    }

    #[Test]
    public function testWithUserInfoEmptyUserStringRemovesUserData(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de:8080');
        $cloned = $url->withUserInfo('');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://www.artemeon.de:8080', $cloned->__toString());
        self::assertEmpty($cloned->getUserInfo());
    }

    #[Test]
    public function testWithUserInfoWithUserStringSetsValidUserInfo(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $cloned = $url->withUserInfo('user');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://user@www.artemeon.de', $cloned->__toString());
        self::assertSame('user', $cloned->getUserInfo());
    }

    #[Test]
    public function testWithUserInfoWithUserStringAndPasswordSetsValidUserInfo(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $cloned = $url->withUserInfo('user', 'password');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://user:password@www.artemeon.de', $cloned->__toString());
        self::assertSame('user:password', $cloned->getUserInfo());
    }

    #[Test]
    public function testWithHostIsNotStringThrowsException(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $this->expectException(InvalidArgumentException::class);

        $url->withHost(123);
    }

    #[Test]
    public function testWithHostIsUpperCaseWillConvertedToLoweCase(): void
    {
        $url = Uri::fromString('http://www.artemeon.de');
        $cloned = $url->withHost('ARTEMEON.COM');

        self::assertNotSame($url, $cloned);
        self::assertSame('artemeon.com', $cloned->getHost());
    }
}
