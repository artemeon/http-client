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
    public function fromFields_CreatesValidHeaders(): void
    {
        $this->headers = Headers::fromFields([UserAgent::fromString('test')]);
        $userAgent = $this->headers->get(HeaderField::USER_AGENT);

        self::assertCount(1, $this->headers);
        self::assertSame(HeaderField::USER_AGENT, $userAgent->getFieldName());
        self::assertSame('test', $userAgent->getValue());
    }

    /**
     * @test
     */
    public function has_IsCaseIncentive_ReturnsTrue(): void
    {
        $this->headers->add(Header::fromField(UserAgent::fromString()));
        self::assertTrue($this->headers->has('USER-AGENT'));
    }

    /**
     * @test
     */
    public function has_NotExists_ReturnsFalse(): void
    {
        $this->headers->add(Header::fromField(UserAgent::fromString()));
        self::assertFalse($this->headers->has('not-exists'));
    }

    /**
     * @test
     */
    public function get_NotExists_ThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->headers->get('not-exists');
    }

    /**
     * @test
     */
    public function get_Exists_ReturnsValue(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertSame($expected, $this->headers->get(HeaderField::AUTHORIZATION));
    }

    /**
     * @test
     */
    public function get_ExistsCaseIncentive_ReturnsValue(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertSame($expected, $this->headers->get('AUTHORIZATION'));
    }

    /**
     * @test
     */
    public function add_Exists_ThrowsException(): void
    {
        $header = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));

        $this->expectException(InvalidArgumentException::class);
        $this->headers->add($header);
        $this->headers->add($header);
    }

    /**
     * @test
     */
    public function add_IsHostHeader_ShouldBeFirstHeader(): void
    {
        $AuthHeader = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $hostHeader = Header::fromField(Host::fromUri(Uri::fromString('ftp://www.artemeon.de')));

        $this->headers->add($AuthHeader);
        $this->headers->add($hostHeader);

        self::assertSame($hostHeader, $this->headers->getIterator()->current());
    }

    /**
     * @test
     */
    public function replace_CaseIncentive_ReplaceHeader(): void
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

    /**
     * @test
     */
    public function replace_IsNotExistentHostHeader_ReplaceAsFirstHeader(): void
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

    /**
     * @test
     */
    public function isEmpty_FieldExists_ReturnsTrue(): void
    {
        $expected = Header::fromString(HeaderField::AUTHORIZATION, '');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty(HeaderField::AUTHORIZATION));
    }

    /**
     * @test
     */
    public function isEmpty_FieldDoesNotExists_ReturnsTrue(): void
    {
        $expected = Header::fromString(HeaderField::AUTHORIZATION, 'some-credentials');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty('does-not-exists'));
    }


    /**
     * @test
     */
    public function isEmpty_FieldExistsCaseIncentive_ReturnsTrue(): void
    {
        $expected = Header::fromString('Authorization', '');
        $this->headers->add($expected);

        self::assertTrue($this->headers->isEmpty('AUTHoriZATION'));
    }

    /**
     * @test
     */
    public function remove_FieldDoesNotExists_DoesNothing(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove('does-not-exists');

        self::assertCount(1 , $this->headers);
    }

    /**
     * @test
     */
    public function remove_FieldExists_RemovesField(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove(HeaderField::USER_AGENT);

        self::assertCount(0 , $this->headers);
    }

    /**
     * @test
     */
    public function remove_FieldExistsCaseIncentive_RemovesField(): void
    {
        $expected = Header::fromField(UserAgent::fromString());
        $this->headers->add($expected);
        $this->headers->remove('USER-AGENT');

        self::assertCount(0 , $this->headers);
    }

    /**
     * @test
     */
    public function getIterator_ReturnsArrayIterator(): void
    {
        $expected = Header::fromField(Authorization::forAuthBasic('john.doe', 'geheim'));
        $this->headers->add($expected);

        self::assertCount(1, $this->headers->getIterator());
    }
}
