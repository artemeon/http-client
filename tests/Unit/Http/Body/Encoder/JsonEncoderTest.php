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

namespace Artemeon\HttpClient\Tests\Unit\Http\Body\Encoder;

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Body\Encoder\JsonEncoder;
use Artemeon\HttpClient\Http\MediaType;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
class JsonEncoderTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close(); // Mockery aufräumen
    }

    public function testFromArrayJsonEncodeFailsThrowsException(): void
    {
        $mockJsonEncode = Mockery::mock('overload:json_encode');
        $mockJsonEncode->shouldReceive('__invoke')->andReturn(false);

        $encoder = JsonEncoder::fromArray(['invalid' => "\xB1\x31"], 0);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't encode to json: Malformed UTF-8 characters, possibly incorrectly encoded");

        $encoder->encode();
    }

    public function testFromObjectReturnExpectedValue(): void
    {
        $class = new stdClass();
        $class->name = 'name';
        $class->password = 'password';

        $encoder = JsonEncoder::fromObject($class);

        self::assertSame('{"name":"name","password":"password"}', $encoder->encode()->__toString());
        self::assertSame(MediaType::JSON, $encoder->getMimeType());
    }

    public function testFromArrayReturnExpectedValue(): void
    {
        $encoder = JsonEncoder::fromArray(
            [
                'name' => 'name',
                'test' => 1,
            ],
        );

        self::assertSame('{"name":"name","test":1}', $encoder->encode()->__toString());
        self::assertSame(MediaType::JSON, $encoder->getMimeType());
    }
}
