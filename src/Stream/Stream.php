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

namespace Artemeon\HttpClient\Stream;

use Artemeon\HttpClient\Exception\RuntimeException;

/**
 * Stream interface implementation for large strings and files.
 *
 * @see https://www.php.net/manual/de/intro.stream.php
 */
class Stream implements AppendableStream
{
    private $resource;

    /**
     * @see https://www.php.net/manual/de/function.stream-get-meta-data
     */
    private array $metaData;

    /**
     * @param resource $resource
     * @throws RuntimeException
     */
    private function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Invalid resource');
        }

        $this->resource = $resource;
        $this->metaData = stream_get_meta_data($resource);
    }

    /**
     * Force to close the file handle.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Named constructor to create an instance based on the given string.
     *
     * @param string $string String content
     * @param string $mode @see https://www.php.net/manual/de/function.fopen.php
     * @throws RuntimeException
     */
    public static function fromString(string $string, string $mode = 'r+'): self
    {
        $resource = fopen('php://temp', $mode);
        $instance = new self($resource);
        $instance->write($string);

        return $instance;
    }

    /**
     * Named constructor to create an instance based on the given file mode.
     *
     * @param string $mode Stream Modes: @see https://www.php.net/manual/de/function.fopen.php
     * @throws RuntimeException
     */
    public static function fromFileMode(string $mode): self
    {
        $resource = fopen('php://temp', $mode);

        return new self($resource);
    }

    /**
     * Named constructor to create an instance based on the given file and read/write mode.
     *
     * @param string $file Path to the file
     * @param string $mode Stream Modes: @see https://www.php.net/manual/de/function.fopen.php
     * @throws RuntimeException
     */
    public static function fromFile(string $file, string $mode = 'r+'): self
    {
        $resource = fopen($file, $mode);

        if (!is_resource($resource)) {
            throw new RuntimeException("Cam't open file $file");
        }

        return new self($resource);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __toString(): string
    {
        try {
            $this->rewind();
            $content = $this->getContents();
        } catch (RuntimeException) {
            $content = '';
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function appendStream(AppendableStream $stream): int
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsWriteable();

        if (!$stream->isReadable()) {
            throw new RuntimeException("Can't append not readable stream");
        }

        $stream->rewind();
        $bytes = stream_copy_to_stream($stream->getResource(), $this->resource);

        if ($bytes === false) {
            throw new RuntimeException('Append failed');
        }

        return $bytes;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        if (!is_resource($this->resource)) {
            return;
        }

        fclose($this->resource);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function detach(): void
    {
        $this->close();
        $this->metaData = [];
        $this->resource = null;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getSize()
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $fstat = fstat($this->resource);

        return $fstat['size'];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function tell()
    {
        $this->assertStreamIsNotDetached();
        $position = ftell($this->resource);

        if ($position === false) {
            throw new RuntimeException("Can't determine position");
        }

        return (int) $position;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function eof()
    {
        if (!is_resource($this->resource)) {
            // php.net doc: feof returns TRUE if the file pointer is at EOF or an error occurs
            return true;
        }

        return feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isSeekable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        // According to the fopen manual mode 'a' and 'a+' are not seekable
        foreach (['a', 'a+'] as $mode) {
            if (str_contains((string) $this->metaData['mode'], $mode)) {
                return false;
            }
        }

        return (bool) $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->assertStreamIsNotDetached();
        $result = fseek($this->resource, $offset, $whence);

        if ($result === -1) {
            throw new RuntimeException("Cant't seek with offset $offset");
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function rewind(): void
    {
        $this->assertStreamIsNotDetached();

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function write($string)
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsWriteable();

        $bytes = fwrite($this->resource, (string) $string);

        if ($bytes === false) {
            throw new RuntimeException("Cant't write to stream");
        }

        return $bytes;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isWritable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $writeModes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];

        foreach ($writeModes as $mode) {
            if (str_contains((string) $this->metaData['mode'], $mode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isReadable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $readModes = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];

        foreach ($readModes as $mode) {
            if (str_contains((string) $this->metaData['mode'], $mode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function read($length)
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsReadable();

        $string = fread($this->resource, (int) $length);

        if ($string === false) {
            throw new RuntimeException("Can't read from stream");
        }

        return $string;
    }

    /**
     * @inheritDoc
     *
     * This function reads the complete stream from the CURRENT! file pointer. If you
     * want ensure to read the complete stream use __toString() instead.
     */
    #[\Override]
    public function getContents()
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsReadable();

        $content = stream_get_contents($this->resource);

        if ($content === false) {
            throw new RuntimeException("Can't read content from stream");
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metaData;
        }

        return $this->metaData[$key] ?? null;
    }

    /**
     * @throws RuntimeException
     */
    private function assertStreamIsNotDetached(): void
    {
        if ($this->resource === null) {
            throw new RuntimeException('Stream is detached');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function assertStreamIsReadable(): void
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function assertStreamIsWriteable(): void
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writeable');
        }
    }
}
