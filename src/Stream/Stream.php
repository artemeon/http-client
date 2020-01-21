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

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Stream interface implementation for large strings and files
 *
 * @see https://www.php.net/manual/de/intro.stream.php
 */
class Stream implements StreamInterface
{
    /** @var resource */
    private $resource;

    /**
     * @var array[]
     * @see https://www.php.net/manual/de/function.stream-get-meta-data
     */
    private $metaData;

    /**
     * Stream constructor.
     *
     * @param resource $resource
     *
     * @throws RuntimeException
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Invalid resource');
        }

        $this->resource = $resource;
        $this->metaData = stream_get_meta_data($resource);
    }

    /**
     * Named constructor to create an instance based on the given string
     *
     * @param string $string
     *
     * @throws RuntimeException
     */
    public static function fromString(string $string): self
    {
        $resource = fopen("php://temp", 'rw');

        $instance = new self($resource);
        $instance->write($string);

        return $instance;
    }

    /**
     * Named constructor to create an instance based on the given file an read/write mode
     *
     * @param string $file Path to the file
     * @param string $mode Stream Modes: @see https://www.php.net/manual/de/function.fopen.php
     *
     * @throws RuntimeException;
     */
    public static function fromFile(string $file, $mode = 'r+'): self
    {
        $resource = fopen($file, $mode);

        if (is_resource($resource)) {
            throw new RuntimeException("Cam't open file $file");
        }

        return new self($resource);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $this->assertStreamIsNotDetached();
        $this->rewind();
        return $this->getContents();
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->assertStreamIsNotDetached();
        fclose($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        $this->close();
        $this->metaData = [];
        $this->resource = null;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function getSize()
    {
        $this->assertStreamIsNotDetached();
        $fstat = fstat($this->resource);

        if ($fstat['size'] < 0 || !isset($fstat['size'])) {
            return null;
        }

        return ($fstat['size']);
    }

    /**
     * @inheritDoc
     */
    public function tell()
    {
        $this->assertStreamIsNotDetached();
        $position = ftell($this->resource);

        if ($position === false) {
            throw new RuntimeException("Can't determine postition");
        }

        return $position;
    }

    /**
     * @inheritDoc
     */
    public function eof()
    {
        $this->assertStreamIsNotDetached();
        feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        $this->assertStreamIsNotDetached();

        // According to the fopen manual mode 'a' and 'a+' are not seekable
       foreach (['a', 'a+'] as $mode) {
            if (strpos($this->metaData["mode"], $mode) >= 0) {
                return false;
            }
        }

        return $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->assertStreamIsNotDetached();
        $result = fseek($this->resource, $offset, $whence);

        if ($result === -1) {
            throw new RuntimeException("Cant't seek with offset $offset");
        }
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function rewind()
    {
        $this->assertStreamIsNotDetached();
        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $this->seek(0);
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function isWritable()
    {
        $this->assertStreamIsNotDetached();
        $writeModes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];

        foreach ($writeModes as $mode) {
            if (strpos($this->metaData["mode"], $mode)  >= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function write($string)
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsWriteable();

        $bytes = fwrite($this->resource, $string);

        if ($bytes === false) {
            throw new RuntimeException("Cant't write to stream");
        }

        return $bytes;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function isReadable()
    {
        $this->assertStreamIsNotDetached();
        $readModes = ['r', 'r+', 'w+', 'a+', 'x', 'x+', 'c+'];

        foreach ($readModes as $mode) {
            if (strpos($this->metaData["mode"], $mode) >= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function read($length)
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsReadable();

        $string = fread($this->resource, $length);

        if ($string === false) {
            throw  new RuntimeException("Can't read from stream");
        }

        return $string;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function getContents()
    {
        $this->assertStreamIsNotDetached();
        $this->assertStreamIsReadable();

        $content = stream_get_contents($this->resource);

        if ($content === false) {
            throw  new RuntimeException("Can't read content from stream");
        }

        return $content;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function getMetadata($key = null)
    {
        $this->assertStreamIsNotDetached();

        if ($key === null) {
            return $this->metaData;
        }

        if (isset($this->metaData[$key])) {
            return $this->metaData[$key];
        }

        return null;
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
            throw new RuntimeException('Stream is detached');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function assertStreamIsWriteable(): void
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is detached');
        }
    }
}
