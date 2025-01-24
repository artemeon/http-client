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

namespace Artemeon\HttpClient\Tests\Unit\Http\Header;

use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Header\Fields\Authorization;
use Artemeon\HttpClient\Http\Header\Fields\Host;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Uri;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Headers::class)]
class HeadersTest extends TestCase
{
    /** @var Headers */
    private $headers;

    #[Override]
    public function setUp(): void
    {
        $this->headers = Headers::create();
    }

    #[Test]
    public function fromFieldsCreatesValidHeaders(): void
    {
        $this->headers = Headers::fromFields([UserAgent::fromString('test')]);
        $userAgent = $this->headers->get(HeaderField::USER_AGENT);

        self::assertCount(1, $this->headers);
        self::assertSame(HeaderField::USER_AGENT, $userAgent->getFieldName());
        self::assertSame('test', $userAgent->getValue());
    }

    #[Test]
    public function hasIsCaseIncentiveReturnsTrue(): void
    {
        $this->headers->add(Header::fromField(UserAgent::fromString()));
        self::assertTrue($this->headers->has('USER-AGENT'));
    }

    #[Test]
    public function hasNotExistsReturnsFalse(): void
    {
        $this->headers->add(Header::fromField(UserAgent::fromString()));
        self::assertFalse($this->headers->has('not-exists'));
    }

    #[Test]
    public function getNotExistsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->get('not-exists');
    }

    #[Test]
    public function getExistsReturnsValue(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertSame($expected, $this->headers->get(HeaderField::AUTHORIZATION));
    }

    #[Test]
    public function getExistsCaseIncentiveReturnsValue(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertSame($expected, $this->headers->get('AUTHORIZATION'));
    }

    #[Test]
    public function addExistsThrowsException(): void
    {
        $header = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));

        $this->expectException(InvalidArgumentException::class);
        $this->headers->add($header);
        $this->headers->add($header);
    }

    #[Test]
    public function addIsHostHeaderShouldBeFirstHeader(): void
    {
        $AuthHeader = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $hostHeader = Header::fromField(Host::fromUri(Uri::fromString('ftp://www.artemeon.de')));

        $this->headers->add($AuthHeader);
        $this->headers->add($hostHeader);

        self::assertSame($hostHeader, $this->headers->getIterator()->current());
    }

    #[Test]
    public function replaceCaseIncentiveReplaceHeader(): void
    {
        $AuthHeader = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $hostHeader = Header::fromField(Host::fromUri(Uri::fromString('ftp://www.artemeon.de')));
        $newHHostHeader = Header::fromString('HOSt', 'http://www.artemeon.de/test.php');

        $this->headers->add($AuthHeader);
        $this->headers->add($hostHeader);

        $this->headers->replace($newHHostHeader);

        self::assertCount(2, $this->headers);
        self::assertSame($newHHostHeader, $this->headers->get(HeaderField::HOST));
    }

    #[Test]
    public function replaceIsNotExistentHostHeaderReplaceAsFirstHeader(): void
    {
        $AuthHeader = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $hostHeader = Header::fromField(UserAgent::fromString());
        $newHHostHeader = Header::fromString('HOSt', 'http://www.artemeon.de/test.php');

        $this->headers->add($AuthHeader);
        $this->headers->add($hostHeader);

        $this->headers->replace($newHHostHeader);

        self::assertCount(3, $this->headers);
        self::assertSame($newHHostHeader, $this->headers->get(HeaderField::HOST));
        self::assertSame($newHHostHeader, $this->headers->getIterator()->current());
    }

    #[Test]
    public function isEmptyFieldExistsReturnsTrue(): void
    {
        $expected = Header::fromString(HeaderField::AUTHORIZATION, '');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty(HeaderField::AUTHORIZATION));
    }

    #[Test]
    public function isEmptyFieldDoesNotExistsReturnsTrue(): void
    {
        $expected = Header::fromString(HeaderField::AUTHORIZATION, 'some-credentials');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty('does-not-exists'));
    }

    #[Test]
    public function isEmptyFieldExistsCaseIncentiveReturnsTrue(): void
    {
        $expected = Header::fromString('Authorization', '');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty('AUTHoriZATION'));
    }

    #[Test]
    public function removeFieldDoesNotExistsDoesNothing(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove('does-not-exists');

        self::assertCount(1, $this->headers);
    }

    #[Test]
    public function removeFieldExistsRemovesField(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove(HeaderField::USER_AGENT);

        self::assertCount(0, $this->headers);
    }

    #[Test]
    public function removeFieldExistsCaseIncentiveRemovesField(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove('USER-AGENT');

        self::assertCount(0, $this->headers);
    }

    #[Test]
    public function getIteratorReturnsArrayIterator(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertCount(1, $this->headers->getIterator());
    }
}
