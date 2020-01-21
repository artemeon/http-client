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
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->metaData = stream_get_meta_data($resource);
    }

    public static function fromString(string $string): self
    {
    }

    public static function fromFile($file)
    {
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        // TODO: Implement detach() method.
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        $fstat = fstat($this->resource);

        if ($fstat['size'] > 0) {
            return null;
        }

        return ($fstat['size']);
    }

    /**
     * @inheritDoc
     */
    public function tell()
    {
        return ftell($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function eof()
    {
        feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $result = fseek($this->resource, $offset, $whence);

        if ($result === -1) {
            throw new RuntimeException("Cant't seek with offset $offset");
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable()
    {
        $writeModes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+',];
    }

    /**
     * @inheritDoc
     */
    public function write($string)
    {
        $bytes = fwrite($this->resource, $string);

        if ($bytes === false) {
            throw new RuntimeException("Cant't write to stream");
        }

        return $bytes;
    }

    /**
     * @inheritDoc
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
    }

    /**
     * @inheritDoc
     */
    public function read($length)
    {
        $string = fread($this->resource, $length);

        if ($string === false) {
            throw  new RuntimeException("Can't read from stream");
        }

        return $string;
    }

    /**
     * @inheritDoc
     */
    public function getContents()
    {
        $content = fpassthru($this->resource);

        if ($content === false) {
            throw  new RuntimeException("Can't read content from stream");
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metaData;
        }

        if (isset($this->metaData[$key])) {
            return $this->metaData[$key];
        }

        return null;
    }
}