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

namespace Artemeon\HttpClient\Http\Body\Reader;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Reader to read body content from local and remote file system
 */
class FileReader implements Reader
{
    /** @var StreamInterface */
    private $stream;

    /** @var string */
    private $file;

    /**
     * FileReader constructor.
     *
     * @param StreamInterface $stream
     * @throws HttpClientException
     */
    public function __construct(StreamInterface $stream, string $file)
    {
        if (!$stream->isReadable()) {
            throw new HttpClientException('Stream is nor readable');
        }

        $this->stream = $stream;
        $this->file = $file;
    }

    /**
     * Named construct to create an instance based on the given file and optional stream context options
     *
     * @param string $file Filename inclusive path
     * @throws HttpClientException
     */
    public static function fromFile(string $file): self
    {
        return new self(Stream::fromFile($file, 'r'), $file);
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension(): string
    {
        if (!preg_match("/\.([a-zA-Z]+)$/", $this->file, $matches)) {
            return '';
        }

        return $matches[1];
    }
}
