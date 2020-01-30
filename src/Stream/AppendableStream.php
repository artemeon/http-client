<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Interface for appendable streams
 */
interface AppendableStream extends StreamInterface
{
    /**
     * Append the given stream to this stream and return thr number of byte appended
     *
     * @param AppendableStream $stream
     * @throws RuntimeException
     */
    public function appendStream(AppendableStream $stream): int;

    /**
     * Return the resource handle
     *
     * @return resource Stream resource handle
     */
    public function getResource();
}
