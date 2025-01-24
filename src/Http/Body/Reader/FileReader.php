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

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Reader to read body content from local and remote file system.
 */
class FileReader implements Reader
{
    private readonly StreamInterface $stream;

    /**
     * @param StreamInterface $stream The content stream
     * @param string $file The file path with file extension
     * @throws RuntimeException
     */
    public function __construct(StreamInterface $stream, private readonly string $file)
    {
        if (!$stream->isReadable()) {
            throw new RuntimeException('Stream is nor readable');
        }

        $this->stream = $stream;
    }

    /**
     * Named construct to create an instance based on the given file path string.
     *
     * @param string $file Filename inclusive path and extension
     * @throws RuntimeException
     */
    public static function fromFile(string $file): self
    {
        return new self(Stream::fromFile($file, 'r'), $file);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFileExtension(): string
    {
        if (!preg_match("/\.([a-zA-Z]+)$/", $this->file, $matches)) {
            return '';
        }

        return $matches[1];
    }
}
