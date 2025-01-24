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
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

/**
 * @internal
 */
#[CoversClass(JsonEncoder::class)]
class JsonEncoderTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    public function fromArrayJsonEncodeFailsThrowsException(): void
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

    #[Test]
    public function fromObjectReturnExpectedValue(): void
    {
        $class = new stdClass();
        $class->name = 'name';
        $class->password = 'password';

        $encoder = JsonEncoder::fromObject($class);

        self::assertSame('{"name":"name","password":"password"}', $encoder->encode()->__toString());
        self::assertSame(MediaType::JSON, $encoder->getMimeType());
    }

    #[Test]
    public function fromArrayReturnExpectedValue(): void
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
