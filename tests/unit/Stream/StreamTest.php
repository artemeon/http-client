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

namespace Artemeon\HttpClient\Tests\Stream;

use Artemeon\HttpClient\Stream\Stream;
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * Class StreamTest
 */
class StreamTest extends TestCase
{
    /** @var Stream */
    private $stream;

    /** @var ProphecyInterface */
    private $globalProphecy;

    /** @var PHPProphet */
    private $globalProphet;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->globalProphet = new PHPProphet();
        $this->globalProphecy = $this->globalProphet->prophesize('\Artemeon\HttpClient\Stream');
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->globalProphet->checkPredictions();
    }

    /**
     * @test
     */
    public function __toString_IsDetached_ReturnEmptyString(): void
    {
        $this->stream = Stream::fromString('some_content');
        $this->stream->detach();
        $content = $this->stream->__toString();

        self::assertEmpty($content);
    }

    /**
     * @test
     */
    public function __toString_ReturnValidString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    /**
     * @test
     */
    public function __toString_WithBytesRead_ReturnsCompleteString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $this->stream->read(12);

        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @runInSeparateProcess
     */
    public function close_IsDetached_ShouldNotCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->shouldBeCalledTimes(1);
        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->stream->close();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @runInSeparateProcess
     */
    public function close_ShouldCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            function ($args) {
                return fclose($args[0]);
            }
        )->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->stream->close();
        $this->stream->eof();
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function detach_ShouldCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            function ($args) {
                return fclose($args[0]);
            }
        )->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertSame([], $this->stream->getMetadata());
    }

    /**
     * @test
     */
    public function getSize_ReturnExpectedValue(): void
    {
        $this->globalProphecy->fstat(Argument::type('resource'))->will(
            function ($args) {
                return fstat($args[0]);
            }
        )->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromString('content');

        self::assertSame(7, $this->stream->getSize());
    }
}
