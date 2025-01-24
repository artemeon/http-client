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

namespace Artemeon\HttpClient\Tests\Unit\Stream;

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Stream\Stream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Override;
use phpmock\prophecy\PHPProphet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @internal
 */
#[CoversClass(Stream::class)]
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
    #[Override]
    public function setUp(): void
    {
        $this->globalProphet = new PHPProphet();
        $this->globalProphecy = $this->globalProphet->prophesize('\Artemeon\HttpClient\Stream');
        $this->filesystem = vfsStream::setup('stream');

        vfsStream::copyFromFileSystem(
            __DIR__ . '/../../Fixtures/encoder',
            $this->filesystem,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function tearDown(): void
    {
        $this->globalProphet->checkPredictions();
        $this->stream = null;
    }

    #[Test]
    #[RunInSeparateProcess]
    public function constructResourceIsInvalidThrowsException(): void
    {
        $this->globalProphecy->fopen(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();
        $this->expectException(RuntimeException::class);

        $this->stream = Stream::fromFileMode('rw');
    }

    #[Test]
    public function appendStreamIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    #[Test]
    public function appendStreamIsNotWriteableThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is not writeable');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    #[Test]
    public function appendStreamGivenStreamIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage("Can't append not readable stream");

        $writeOnlyStream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->stream->appendStream($writeOnlyStream);
    }

    #[Test]
    public function appendStreamCantCopyStreamThrowsException(): void
    {
        $this->globalProphecy->stream_copy_to_stream(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('test');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Append failed');

        $writeOnlyStream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->appendStream($writeOnlyStream);
    }

    #[Test]
    public function appendStreamReturnsAppendedStream(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->appendStream(Stream::fromString('_appended'));

        self::assertSame('test_appended', $this->stream->__toString());
    }

    #[Test]
    #[RunInSeparateProcess]
    public function fromFileResourceIsInvalidThrowsException(): void
    {
        $this->globalProphecy->fopen(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->globalProphecy->reveal();
        $this->expectException(RuntimeException::class);

        $this->stream = Stream::fromFile('/does/not/exists.txt');
    }

    #[Test]
    public function toStringIsDetachedReturnEmptyString(): void
    {
        $this->stream = Stream::fromString('some_content');
        $this->stream->detach();
        $content = $this->stream->__toString();

        self::assertEmpty($content);
    }

    #[Test]
    public function toStringReturnValidString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    #[Test]
    public function toStringWithBytesReadReturnsCompleteString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $this->stream->read(12);

        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    #[RunInSeparateProcess]
    public function closeIsDetachedShouldNotCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            static fn ($args) => fclose($args[0]),
        )->shouldBeCalledTimes(1);

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->stream->close();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    #[RunInSeparateProcess]
    public function closeShouldCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            static fn ($args) => fclose($args[0]),
        )->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->stream->close();
        $this->stream->eof();
    }

    #[Test]
    #[RunInSeparateProcess]
    public function detachShouldCallClose(): void
    {
        $this->globalProphecy->fclose(Argument::type('resource'))->will(
            static fn ($args) => fclose($args[0]),
        )->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertSame([], $this->stream->getMetadata());
    }

    #[Test]
    #[RunInSeparateProcess]
    public function getSizeReturnExpectedValue(): void
    {
        $this->globalProphecy->fstat(Argument::type('resource'))->will(
            static fn ($args) => fstat($args[0]),
        )->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromString('content');

        self::assertSame(7, $this->stream->getSize());
    }

    #[Test]
    public function getSizeIsDetachedReturnNull(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertNull($this->stream->getSize());
    }

    #[Test]
    public function tellIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->tell();
    }

    #[Test]
    public function tellFtellReturnsFalseThrowsException(): void
    {
        $this->globalProphecy->ftell(Argument::type('resource'))->willReturn(false);
        $this->globalProphecy->reveal();

        $this->stream = Stream::fromString('content');
        $this->expectException(RuntimeException::class);

        $this->stream->tell();
    }

    #[Test]
    public function tellReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->getContents();

        self::assertSame(7, $this->stream->tell());
    }

    #[Test]
    public function eofIsDetachedReturnsTrue(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertTrue($this->stream->eof());
    }

    #[Test]
    public function eofReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        self::assertFalse($this->stream->eof());

        $this->stream->getContents();
        self::assertTrue($this->stream->eof());
    }

    #[Test]
    public function isSeekableIsDetachedReturnFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isSeekable());
    }

    #[Test]
    public function isSeekableWithNonSeekableFileModesReturnFalse(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        self::assertFalse($this->stream->isSeekable());

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a+');
        self::assertFalse($this->stream->isSeekable());
    }

    #[Test]
    public function isSeekableReturnTrue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        self::assertTrue($this->stream->isSeekable());
    }

    #[Test]
    public function seekIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->seek(7);
    }

    #[Test]
    public function seekFseekFailsThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->expectException(RuntimeException::class);

        $this->stream->seek(456);
    }

    #[Test]
    public function seekFseekSetsValidPointer(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->seek(2);

        self::assertSame(2, $this->stream->tell());
    }

    #[Test]
    public function rewindIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->rewind();
    }

    #[Test]
    public function rewindIsNotSeekableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        $this->expectException(RuntimeException::class);

        $this->stream->rewind();
    }

    #[Test]
    public function rewindShouldResetFilePointerToZero(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->stream->getContents();
        $this->stream->rewind();

        self::assertSame(0, $this->stream->tell());
    }

    #[Test]
    public function isWritableIsDetachedReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isWritable());
    }

    #[DataProvider('provideIsModeWriteable')]
    #[Test]
    public function isWritableReturnsExpectedValue(string $mode, bool $isWritable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isWritable, $this->stream->isWritable());
    }

    #[Test]
    public function isReadableIsDetachedReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isReadable());
    }

    #[DataProvider('provideIsReadable')]
    #[Test]
    public function isReadableReturnsExpectedValue(string $mode, bool $isReadable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isReadable, $this->stream->isReadable());
    }

    #[Test]
    public function writeIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r+');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->write('test');
    }

    #[Test]
    public function writeIsNotWriteableThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is not writeable');

        $this->stream->write('test');
    }

    #[Test]
    #[RunInSeparateProcess]
    public function writeFWriteReturnFalseThrowsException(): void
    {
        $this->globalProphecy->fwrite(Argument::type('resource'), 'test')
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();
        $this->stream = Stream::fromFileMode('r+');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage("Cant't write to stream");

        $this->stream->write('test');
    }

    #[Test]
    public function writeReturnNumberOfBytesWritten(): void
    {
        $expectedString = 'Some content string';
        $expectedBytes = strlen($expectedString);
        $this->stream = Stream::fromFileMode('r+');

        self::assertSame($expectedBytes, $this->stream->write($expectedString));
        self::assertSame($expectedString, $this->stream->__toString());
    }

    #[Test]
    public function readIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->read(100);
    }

    #[Test]
    public function readIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is not readable');

        $this->stream->read(100);
    }

    #[Test]
    #[RunInSeparateProcess]
    public function readFReadReturnsFalseThrowsException(): void
    {
        $this->globalProphecy->fread(Argument::type('resource'), 100)
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage("Can't read from stream");

        $this->stream->read(100);
    }

    #[Test]
    public function readReturnValidNumberOfBytes(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r');
        self::assertSame(100, strlen($this->stream->read(100)));
    }

    #[Test]
    public function getContentIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is detached');

        $this->stream->getContents();
    }

    #[Test]
    public function getContentIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Stream is not readable');

        $this->stream->getContents();
    }

    #[Test]
    #[RunInSeparateProcess]
    public function getContentStreamReturnsFalseThrowsException(): void
    {
        $this->globalProphecy->stream_get_contents(Argument::type('resource'))
            ->willReturn(false)
            ->shouldBeCalled();

        $this->globalProphecy->reveal();

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage("Can't read content from stream");

        $this->stream->getContents();
    }

    #[Test]
    public function getMetadataKeyIsNullReturnsCompleteArray(): void
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

    #[Test]
    public function getMetadataWithValidKeyReturnsKeyValue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $mode = $this->stream->getMetadata('mode');

        self::assertSame('r+', $mode);
    }

    #[Test]
    public function getMetadataWithNonExistentKeyReturnsNull(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');

        self::assertNull($this->stream->getMetadata('does_nit_exists'));
    }

    /**
     * All fopen modes for the status isWriteable.
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
     * All fopen modes for the status isReadable.
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
