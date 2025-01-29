<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Stream;

use Artemeon\HttpClient\Exception\RuntimeException;
use Psr\Http\Message\StreamInterface;

/**
 * Interface for appendable streams.
 */
interface AppendableStream extends StreamInterface
{
    /**
     * Append the given stream to this stream and return thr number of byte appended.
     *
     * @throws RuntimeException
     */
    public function appendStream(AppendableStream $stream): int;

    /**
     * Return the resource handle.
     *
     * @return resource
     */
    public function getResource(): mixed;
}
