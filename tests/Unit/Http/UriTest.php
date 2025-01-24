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
    public function fromStringSetValidValues(): void
    {
        $expected = 'http://www.artemeon.de';
        $url = Uri::fromString($expected);
        self::assertSame($expected, $url->__toString());
    }

    #[Test]
    public function getQueryReturnExpectedValue(): void
    {
        $expected = 'user=john.doe';
        $url = Uri::fromQueryParams('http://www.artemeon.de', ['user' => 'john.doe']);
        self::assertSame($expected, $url->getQuery());
    }

    #[Test]
    public function getFragmentReturnExpectedValue(): void
    {
        $expected = 'anker';
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html#' . $expected);
        self::assertSame($expected, $url->getFragment());
    }

    #[Test]
    public function getFragmentReturnsEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/pfad/test.html');
        self::assertSame('', $url->getFragment());
    }

    #[Test]
    public function getUserInfoReturnUserPassword(): void
    {
        $url = Uri::fromString('https://dsi:topsecret@www.artemeon.de');
        self::assertSame('dsi:topsecret', $url->getUserInfo());
    }

    #[Test]
    public function getUserInfoReturnOnlyUser(): void
    {
        $url = Uri::fromString('https://dsi@www.artemeon.de');
        self::assertSame('dsi', $url->getUserInfo());
    }

    #[Test]
    public function getUserInfoReturnEmptyString(): void
    {
        $url = Uri::fromString('https://www.artemeon.de');
        self::assertSame('', $url->getUserInfo());
    }

    #[Test]
    public function getSchemeReturnExpectedValue(): void
    {
        $url = Uri::fromString('ftp://dsi:topsecret@www.artemeon.de');
        self::assertSame('ftp', $url->getScheme());
    }

    #[Test]
    public function getHostReturnExpectedValue(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('www.artemeon.de', $url->getHost());
    }

    #[Test]
    public function getPortReturnExpectedNull(): void
    {
        $url = Uri::fromString('http://www.artemeon.de/path/to/file.html');
        self::assertNull($url->getPort());
    }

    #[Test]
    public function getPortReturnExpectedInt(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame(8080, $url->getPort());
    }

    #[Test]
    public function getPathReturnExpectedString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080/path/to/file.html');
        self::assertSame('/path/to/file.html', $url->getPath());
    }

    #[Test]
    public function getPathReturnExpectedEmptyString(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080');
        self::assertSame('', $url->getPath());
    }

    #[Test]
    public function withSchemeIsNotStringThroesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $url = Uri::fromString('http://www.artemeon.de:8080');
        $url->withScheme(0);
    }

    #[Test]
    public function withSchemeReturnsUpdatedInstance(): void
    {
        $url = Uri::fromString('http://www.artemeon.de:8080');
        $cloned = $url->withScheme('FTP');

        self::assertNotSame($url, $cloned);
        self::assertSame('ftp', $cloned->getScheme());
        self::assertSame('ftp://www.artemeon.de:8080', $cloned->__toString());
    }

    #[Test]
    public function withUserInfoEmptyUserStringRemovesUserData(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de:8080');
        $cloned = $url->withUserInfo('');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://www.artemeon.de:8080', $cloned->__toString());
        self::assertEmpty($cloned->getUserInfo());
    }

    #[Test]
    public function withUserInfoWithUserStringSetsValidUserInfo(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $cloned = $url->withUserInfo('user');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://user@www.artemeon.de', $cloned->__toString());
        self::assertSame('user', $cloned->getUserInfo());
    }

    #[Test]
    public function withUserInfoWithUserStringAndPasswordSetsValidUserInfo(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $cloned = $url->withUserInfo('user', 'password');

        self::assertNotSame($url, $cloned);
        self::assertSame('http://user:password@www.artemeon.de', $cloned->__toString());
        self::assertSame('user:password', $cloned->getUserInfo());
    }

    #[Test]
    public function withHostIsNotStringThrowsException(): void
    {
        $url = Uri::fromString('http://dietmar.simons:password@www.artemeon.de');
        $this->expectException(InvalidArgumentException::class);

        $url->withHost(123);
    }

    #[Test]
    public function withHostIsUpperCaseWillConvertedToLoweCase(): void
    {
        $url = Uri::fromString('http://www.artemeon.de');
        $cloned = $url->withHost('ARTEMEON.COM');

        self::assertNotSame($url, $cloned);
        self::assertSame('artemeon.com', $cloned->getHost());
    }
}
