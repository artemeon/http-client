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

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Stream\Stream;
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

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
     */
    public function close_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);
        $this->stream->close();
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function close_CloseResource(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->shouldBeCalled();
        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->stream->close();
    }


}
