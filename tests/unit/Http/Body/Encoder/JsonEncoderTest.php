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

namespace Artemeon\HttpClient\Tests\Http\Body\Encoder;

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Body\Encoder\JsonEncoder;
use Artemeon\HttpClient\Http\MediaType;
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

/**
 * @covers \Artemeon\HttpClient\Http\Body\Encoder\JsonEncoder
 */
class JsonEncoderTest extends TestCase
{
    /**
     * @test
     * @runInSeparateProcess
     */
    public function fromArray_JsonEncodeFailsThrows_Exception()
    {
        $globalProphet = new PHPProphet();
        $globalProphecy = $globalProphet->prophesize("\Artemeon\HttpClient\Http\Body\Encoder");

        $globalProphecy->json_encode(Argument::any(), Argument::any())->willReturn(false);
        $globalProphecy->reveal();

        $this->expectException(RuntimeException::class);
        $value = ['test' => 12];
        $options = 0;

        $encoder = JsonEncoder::fromArray($value, $options);
        $encoder->encode();

        $globalProphet->checkPredictions();
    }

    /**
     * @test
     */
    public function fromObject_ReturnExpectedValue(): void
    {
        $class = new stdClass();
        $class->name = 'name';
        $class->password = 'password';

        $encoder = JsonEncoder::fromObject($class);

        self::assertSame('{"name":"name","password":"password"}', $encoder->encode()->__toString());
        self::assertSame(MediaType::JSON, $encoder->getMimeType());
    }

    /**
     * @test
     */
    public function fromArray_ReturnExpectedValue(): void
    {
        $encoder = JsonEncoder::fromArray(
            [
                'name' => 'name',
                'test' => 1
            ]
        );

        self::assertSame('{"name":"name","test":1}', $encoder->encode()->__toString());
        self::assertSame(MediaType::JSON, $encoder->getMimeType());
    }
}
