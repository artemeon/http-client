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
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Override;
use php_user_filter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FalseReturningFilter extends php_user_filter
{
    public function filter($in, $out, &$consumed, $closing): int
    {
        return PSFS_ERR_FATAL;
    }
}

class FalseReturningStream {

    public $context;
    public function stream_open($path, $mode, $options, &$opened_path) {
        return true;
    }

    public function stream_stat(): array|false
    {
        return false;
    }

    public function stream_read($count)
    {
        return false;
    }

    public function stream_write($data)
    {
        return false;
    }

    public function stream_eof() {
        return true;
    }
    public function stream_get_contents($resource): string|false
    {
        return false;
    }


}
/**
 * @internal
 */
class StreamTest extends TestCase
{
    private Stream $stream;

    private vfsStreamDirectory $filesystem;

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = vfsStream::setup('stream');
        file_put_contents($this->filesystem->url() . '/generated.json', '');

        vfsStream::copyFromFileSystem(
            __DIR__ . '/../../Fixtures/encoder',
            $this->filesystem,
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function constructResourceIsInvalidThrowsException(): void
    {
        $fopenMock = Mockery::mock('overload:fopen');
        $fopenMock->shouldReceive('__invoke')
            ->with(Mockery::any(), Mockery::any())
            ->andReturn(false);

        $this->expectException(RuntimeException::class);

        Stream::fromFileMode('rw');
    }

    public function testAppendStreamIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    public function testAppendStreamIsNotWriteableThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writeable');

        $this->stream->appendStream(Stream::fromString('append'));
    }

    public function testAppendStreamGivenStreamIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromString('test');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't append not readable stream");

        $writeOnlyStream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->stream->appendStream($writeOnlyStream);
    }

    public function testAppendStreamCantCopyStreamThrowsException(): void
    {
        stream_wrapper_register("falsestream", FalseReturningStream::class);
        $resourceMock = fopen('falsestream://test', 'w');

        $testString ='test';
        $streamFeature = Stream::fromString($testString);

        $streamAppendMock = $this->createMock(Stream::class);
        $streamAppendMock->expects($this->once())
            ->method('getResource')
            ->willReturn($resourceMock);

        $streamAppendMock->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Append failed');

        $streamFeature->appendStream($streamAppendMock);
    }

    public function mockedStreamCopyToStream($source, $destination)
    {
        return call_user_func_array($GLOBALS['originalStreamCopyToStream'], func_get_args());
    }

    public function testAppendStreamReturnsAppendedStream(): void
    {
        $this->stream = Stream::fromString('test');
        $this->stream->appendStream(Stream::fromString('_appended'));

        self::assertSame('test_appended', $this->stream->__toString());
    }

    public function testFromFileResourceIsInvalidThrowsException(): void
    {
        $fopenMock = Mockery::mock('overload:fopen');
        $fopenMock->shouldReceive('__invoke')
            ->with(Mockery::any(), Mockery::any())
            ->andReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t open file /does/not/exists.txt');

        $this->stream = Stream::fromFile('/does/not/exists.txt');
        $this->fail('Expected RuntimeException was not thrown.');
    }

    public function testToStringIsDetachedReturnEmptyString(): void
    {
        $this->stream = Stream::fromString('some_content');
        $this->stream->detach();
        $content = $this->stream->__toString();

        self::assertEmpty($content);
    }

    public function testToStringReturnValidString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    public function testToStringWithBytesReadReturnsCompleteString(): void
    {
        $expected = 'some_content';
        $this->stream = Stream::fromString($expected);
        $this->stream->read(12);

        $content = $this->stream->__toString();

        self::assertSame($expected, $content);
    }

    public function testCloseIsDetachedShouldNotCallClose(): void
    {
        $fcloseMock = Mockery::mock('overload:fclose');
        $fcloseMock->shouldReceive('__invoke')
            ->with(Mockery::type('resource'))
            ->andReturnUsing(static fn ($resource) => fclose($resource));

        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->stream->close();

        Mockery::close();
        $this->addToAssertionCount(1);
    }

    public function testCloseShouldCallClose(): void
    {
        $fcloseMock = Mockery::mock('overload:fclose');
        $fcloseMock->shouldReceive('__invoke')
            ->with(Mockery::type('resource'))
            ->once();

        $this->stream = Stream::fromString('content');
        $this->stream->close();

        Mockery::close();
        $this->addToAssertionCount(1);
    }

    public function testDetachShouldCallClose(): void
    {
        $fcloseMock = Mockery::mock('overload:fclose');
        $fcloseMock->shouldReceive('__invoke')
            ->with(Mockery::type('resource'))
            ->once();

        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertSame([], $this->stream->getMetadata());
    }

    public function testGetSizeReturnExpectedValue(): void
    {
        $fstatMock = Mockery::mock('overload:fstat');
        $fstatMock->shouldReceive('__invoke')
            ->with(Mockery::type('resource'))
            ->once();

        $this->stream = Stream::fromString('content');

        self::assertSame(7, $this->stream->getSize());
    }

    public function testGetSizeIsDetachedReturnNull(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertNull($this->stream->getSize());
    }

    public function testTellIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $this->stream->tell();
    }

    public function testTellFtellReturnsFalseThrowsException(): void
    {
        // generate resource and set file pointer to not allowed position
        $resourceMock = fopen('php://memory', 'r+');
        fwrite($resourceMock, 'test content');
        fseek($resourceMock, -1);

        $streamHandler = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource'])
            ->getMock();

        $streamHandler->expects($this->exactly(2))->method('getResource')->willReturn($resourceMock);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t determine position');

        $streamHandler->tell();
    }

    public function testTellReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->getContents();

        self::assertSame(7, $this->stream->tell());
    }

    public function testEofIsDetachedReturnsTrue(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertTrue($this->stream->eof());
    }

    public function testEofReturnsExpectedValued(): void
    {
        $this->stream = Stream::fromString('content');
        self::assertFalse($this->stream->eof());

        $this->stream->getContents();
        self::assertTrue($this->stream->eof());
    }

    public function testIsSeekableIsDetachedReturnFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isSeekable());
    }

    public function testIsSeekableWithNonSeekableFileModesReturnFalse(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        self::assertFalse($this->stream->isSeekable());

        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a+');
        self::assertFalse($this->stream->isSeekable());
    }

    public function testIsSeekableReturnTrue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        self::assertTrue($this->stream->isSeekable());
    }

    public function testSeekIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->seek(7);
    }

    public function testSeekFseekFailsThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->expectException(RuntimeException::class);

        $this->stream->seek(-456);
    }

    public function testSeekFseekSetsValidPointer(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->seek(2);

        self::assertSame(2, $this->stream->tell());
    }

    public function testRewindIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->rewind();
    }

    public function testRewindIsNotSeekableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'a');
        $this->expectException(RuntimeException::class);

        $this->stream->rewind();
    }

    public function testRewindShouldResetFilePointerToZero(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $this->stream->getContents();
        $this->stream->rewind();

        self::assertSame(0, $this->stream->tell());
    }

    public function testIsWritableIsDetachedReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isWritable());
    }

    #[DataProvider('provideIsModeWriteable')]
    public function testIsWritableReturnsExpectedValue(string $mode, bool $isWritable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isWritable, $this->stream->isWritable());
    }

    public function testIsReadableIsDetachedReturnsFalse(): void
    {
        $this->stream = Stream::fromString('content');
        $this->stream->detach();

        self::assertFalse($this->stream->isReadable());
    }

    #[DataProvider('provideIsReadable')]
    public function testIsReadableReturnsExpectedValue(string $mode, bool $isReadable, string $file): void
    {
        $file = $this->filesystem->url() . '/' . $file;
        $this->stream = Stream::fromFile($file, $mode);

        self::assertSame($isReadable, $this->stream->isReadable());
    }

    public function testWriteIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r+');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);

        $this->stream->write('test');
    }

    public function testWriteIsNotWriteableThrowsException(): void
    {
        $this->stream = Stream::fromFileMode('r');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writeable');

        $this->stream->write('test');
    }

    public function testWriteFWriteReturnFalseThrowsException(): void
    {
        // generate read only resource
        $resourceMock = fopen('php://memory', 'r');

        $streamHandler = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'isWritable'])
            ->getMock();

        $streamHandler->expects($this->once())
            ->method('isWritable')
            ->willReturn(true);
        $streamHandler->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceMock);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t write to stream');

        $streamHandler->write('test');
    }

    public function testWriteReturnNumberOfBytesWritten(): void
    {
        $expectedString = 'Some content string';
        $expectedBytes = strlen($expectedString);
        $this->stream = Stream::fromFileMode('r+');

        self::assertSame($expectedBytes, $this->stream->write($expectedString));
        self::assertSame($expectedString, $this->stream->__toString());
    }

    public function testReadIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $this->stream->read(100);
    }

    public function testReadIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $this->stream->read(100);
    }

    public function testReadFReadReturnsFalseThrowsException(): void
    {
        stream_filter_register('false_filter', FalseReturningFilter::class);
        $resourceMock = fopen('php://memory', 'r+');
        fwrite($resourceMock, 'Some content string');
        stream_filter_append($resourceMock, 'false_filter');

        $streamHandler = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'isReadable'])
            ->getMock();

        $streamHandler->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $streamHandler->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceMock);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t read from stream');

        $streamHandler->read(100);
    }

    public function testReadReturnValidNumberOfBytes(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r');
        self::assertSame(100, strlen($this->stream->read(100)));
    }

    public function testGetContentIsDetachedThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json');
        $this->stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $this->stream->getContents();
    }

    public function testGetContentIsNotReadableThrowsException(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'w');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $this->stream->getContents();
    }

    public function testGetContentStreamReturnsFalseThrowsException(): void
    {
        $this->markTestIncomplete('mock');
        stream_wrapper_register("falsestream", FalseReturningStream::class);
        $resourceMock = fopen('falsestream://test', 'w');

        $streamHandler = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource', 'isReadable'])
            ->getMock();

        $streamHandler->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $streamHandler->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceMock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can't read content from stream");

        $streamHandler->getContents();
    }

    public function testGetMetadataKeyIsNullReturnsCompleteArray(): void
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

    public function testGetMetadataWithValidKeyReturnsKeyValue(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');
        $mode = $this->stream->getMetadata('mode');

        self::assertSame('r+', $mode);
    }

    public function testGetMetadataWithNonExistentKeyReturnsNull(): void
    {
        $this->stream = Stream::fromFile($this->filesystem->url() . '/generated.json', 'r+');

        self::assertNull($this->stream->getMetadata('does_nit_exists'));
    }

    /**
     * All fopen modes for the status isWriteable.
     */
    public static function provideIsModeWriteable(): array
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
    public static function provideIsReadable(): array
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
