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

use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\HeaderField;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Artemeon\HttpClient\Http\Header\Header
 * @internal
 */
class HeaderTest extends TestCase
{
    /**
     * @test
     */
    public function getValueReturnStringWithoutComma(): void
    {
        $header = Header::fromString(HeaderField::REFERER, 'some-referer');
        self::assertSame('some-referer', $header->getValue());
    }

    /**
     * @test
     */
    public function getValueReturnCommaSeparatedString(): void
    {
        $header = Header::fromArray(HeaderField::REFERER, ['some-referer', 'more-stuff']);
        self::assertSame('some-referer, more-stuff', $header->getValue());
    }

    /**
     * @test
     */
    public function addValueAddToArray(): void
    {
        $header = Header::fromString(HeaderField::REFERER, 'some-referer');
        $header->addValue('added-string');
        self::assertSame('some-referer, added-string', $header->getValue());
    }

    /**
     * @test
     */
    public function getValuesReturnsExceptedArray(): void
    {
        $header = Header::fromArray(HeaderField::REFERER, ['some-referer', 'more-stuff']);
        self::assertSame('some-referer', $header->getValues()[0]);
        self::assertSame('more-stuff', $header->getValues()[1]);
    }

    /**
     * @test
     */
    public function getFieldNameReturnsExpectedValue(): void
    {
        $header = Header::fromField(UserAgent::fromString());
        self::assertSame(HeaderField::USER_AGENT, $header->getFieldName());
    }
}
