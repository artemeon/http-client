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
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @covers \Artemeon\HttpClient\Stream\Stream
 */
class StreamTest extends TestCase
{
    /** @var Stream */
    private $stream;

    /** @var ProphecyInterface */
    private $globalProphecy;

    /** @var PHPProphet */
    private $globalProphet;

    /** @var vfsStreamDirectory */
    private $filesystem;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->globalProphet = new PHPProphet();
        $this->globalProphecy = $this->globalProphet->prophesize('\Artemeon\HttpClient\Stream');
        $this->filesystem = vfsStream::setup('stream');

        vfsStream::copyFromFileSystem(
            __DIR__ . '/../../fixtures/encoder',
            $this->filesystem
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->globalProphet->checkPredictions();
        $this->stream = null;
    }

    /**
     * @test
     */
    public function __construct_ResourceIsInvalid_ThrowsException()
    {
        $this->globalProphecy->fopen(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();
        $this->expectException(HttpClientException::class);

        $this->stream = Stream::fromFileMode('rw');
    }

    /**
     * @test
     */
    public function appendStream_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    /**
     * @test
     */
    public function appendStream_IsNotWriteable_ThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is not writeable');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    /**
     * @test
     */
    public function appendStream_GivenStreamIsNotReadable_ThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage("Can't append not readable stream");

        $writeOnlyStream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->stream->appendStream($writeOnlyStream);
    }

    /**
     * @test
     */
    public function appendStream_CantCopyStream_ThrowsException(): void
    {
        $this->globalProphecy->stream_copy_to_stream(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('test');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage("Append failed");

        $writeOnlyStream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->appendStream($writeOnlyStream);
    }

    /**
     * @test
     */
    public function appendStream_ReturnsAppendedStream(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->appendStream(Stream::fromString('_appended'));

        self::assertSame('test_appended', $this->stream->__toString());
    }

    /**
     * @test
     */
    public function fromFile_ResourceIsInvalid_ThrowsException(): void
    {
        $this->globalProphecy->fopen(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();
        $this->expectException(HttpClientException::class);

        $this->stream = Stream::fromFile('/does/not/exists.txt');
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
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            function ($args) {
                return fclose($args[0]);
            }
        )->shouldBeCalledTimes(1);

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
     * @runInSeparateProcess 
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

    /**
     * @test
     */
    public function getSize_IsDetached_ReturnNull(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertNull($this->stream->getSize());
    }

    /**
     * @test
     */
    public function tell_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);

        $this->stream->tell();
    }

    /**
     * @test
     */
    public function tell_FtellReturnsFalse_ThrowsException(): void
    {
        $this->globalProphecy->ftell(Argument::type('resource'))->willReturn(false);
        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->expectException(HttpClientException::class);

        $this->stream->tell();
    }

    /**
     * @test
     */
    public function tell_ReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->getContents();

        self::assertSame(7, $this->stream->tell());
    }

    /**
     * @test
     */
    public function eof_IsDetached_ReturnsTrue(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertTrue($this->stream->eof());
    }

    /**
     * @test
     */
    public function eof_ReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        self::assertFalse($this->stream->eof());

        $this->stream->getContents();
        self::assertTrue($this->stream->eof());
    }

    /**
     * @test
     */
    public function isSeekable_IsDetached_ReturnFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isSeekable());
    }

    /**
     * @test
     */
    public function isSeekable_WithNonSeekableFileModes_ReturnFalse(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        self::assertFalse($this->stream->isSeekable());

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a+');
        self::assertFalse($this->stream->isSeekable());
    }

    /**
     * @test
     */
    public function isSeekable_ReturnTrue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        self::assertTrue($this->stream->isSeekable());
    }

    /**
     * @test
     */
    public function seek_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);

        $this->stream->seek(7);
    }

    /**
     * @test
     */
    public function seek_FseekFails_ThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->expectException(HttpClientException::class);

        $this->stream->seek(456);
    }

    /**
     * @test
     */
    public function seek_FseekSetsValidPointer(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->seek(2);

        self::assertSame(2, $this->stream->tell());
    }

    /**
     * @test
     */
    public function rewind_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);

        $this->stream->rewind();
    }

    /**
     * @test
     */
    public function rewind_IsNotSeekable_ThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        $this->expectException(HttpClientException::class);

        $this->stream->rewind();
    }

    /**
     * @test
     */
    public function rewind_ShouldResetFilePointerToZero(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->stream->getContents();
        $this->stream->rewind();

        self::assertSame(0, $this->stream->tell());
    }

    /**
     * @test
     */
    public function isWritable_IsDetached_ReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isWritable());
    }

    /**
     * @test
     * @dataProvider provideIsModeWriteable
     */
    public function isWritable_ReturnsExpectedValue(string $mode, bool $isWritable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isWritable, $this->stream->isWritable());
    }

    /**
     * @test
     */
    public function isReadable_IsDetached_ReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isReadable());
    }

    /**
     * @test
     * @dataProvider provideIsReadable
     */
    public function isReadable_ReturnsExpectedValue(string $mode, bool $isReadable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isReadable, $this->stream->isReadable());
    }

    /**
     * @test
     */
    public function write_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r+');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);

        $this->stream->write('test');
    }

    /**
     * @test
     */
    public function write_IsNotWriteable_ThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is not writeable');

        $this->stream->write('test');
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function write_FWriteReturnFalse_ThrowsException(): void
    {
        $this->globalProphecy->fwrite(Argument::type('resource'), 'test')
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromFileMode('r+');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage("Cant't write to stream");

        $this->stream->write('test');
    }

    /**
     * @test
     */
    public function write_ReturnNumberOfBytesWritten(): void
    {
        $expectedString = 'Some content string';
        $expectedBytes = strlen($expectedString);
        $this->stream = Stream::fromFileMode('r+');

        self::assertSame($expectedBytes, $this->stream->write($expectedString));
        self::assertSame($expectedString, $this->stream->__toString());
    }

    /**
     * @test
     */
    public function read_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->read(100);
    }

    /**
     * @test
     */
    public function read_IsNotReadable_ThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is not readable');

        $this->stream->read(100);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function read_FReadReturnsFalse_ThrowsException(): void
    {
        $this->globalProphecy->fread(Argument::type('resource'), 100)
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage("Can't read from stream");

        $this->stream->read(100);
    }

    /**
     * @test
     */
    public function read_ReturnValidNumberOfBytes(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r');
        self::assertSame(100, strlen($this->stream->read(100)));
    }

    /**
     * @test
     */
    public function getContent_IsDetached_ThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->getContents();
    }

    /**
     * @test
     */
    public function getContent_IsNotReadable_ThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage('Stream is not readable');

        $this->stream->getContents();
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function getContent_StreamReturnsFalse_ThrowsException(): void
    {
        $this->globalProphecy->stream_get_contents(Argument::type('resource'))
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->expectException(HttpClientException::class);
        $this->expectErrorMessage("Can't read content from stream");

        $this->stream->getContents();
    }

    /**
     * @test
     */
    public function getMetadata_KeyIsNull_ReturnsCompleteArray(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $metaData = $this->stream->getMetadata();

        self::assertArrayHasKey('timed_out', $metaData);
        self::assertArrayHasKey('blocked', $metaData);
        self::assertArrayHasKey('eof', $metaData);
        self::assertArrayHasKey('wrapper_data', $metaData);
        self::assertArrayHasKey('wrapper_type', $metaData);
        self::assertArrayHasKey('stream_type', $metaData);
        self::assertArrayHasKey('mode', $metaData);
        self::assertArrayHasKey('unread_bytes', $metaData);
        self::assertArrayHasKey('seekable', $metaData);
        self::assertArrayHasKey('uri', $metaData);
    }

    /**
     * @test
     */
    public function getMetadata_WithValidKey_ReturnsKeyValue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $mode = $this->stream->getMetadata('mode');

        self::assertSame('r+', $mode);
    }

    /**
     * @test
     */
    public function getMetadata_WithNonExistentKey_ReturnsNull(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');

        self::assertNull($this->stream->getMetadata('does_nit_exists'));
    }

    /**
     * All fopen modes for the status isWriteable
     */
    public function provideIsModeWriteable(): array
    {
        return [
            ['r', false, 'generated.json'],
            ['r+', true, 'generated.json'],
            ['w', true, 'generated.json'],
            ['w+', true, 'generated.json'],
            ['a', true, 'generated.json'],
            ['a+', true, 'generated.json'],
            ['x', true, 'new_file.json'],
            ['x+', true, 'new_file.json'],
            ['c', true, 'generated.json'],
            ['c+', true, 'generated.json'],
        ];
    }

    /**
     * All fopen modes for the status isReadable
     */
    public function provideIsReadable(): array
    {
        return [
            ['r', true, 'generated.json'],
            ['r+', true, 'generated.json'],
            ['w', false, 'generated.json'],
            ['w+', true, 'generated.json'],
            ['a', false, 'generated.json'],
            ['a+', true, 'generated.json'],
            ['x', false, 'new_file.json'],
            ['x+', true, 'new_file.json'],
            ['c', false, 'generated.json'],
            ['c+', true, 'generated.json'],
        ];
    }

}
