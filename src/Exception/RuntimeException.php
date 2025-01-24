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

namespace Artemeon\HttpClient\Exception;

use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Base class to catch all possible runtime exceptions.
 *
 * ```
 * 1. RuntimeException (All possible exceptions inclusive during instantiation)
 *   1. TransferException (All exception during request/response transfer)
 *     1. ConnectException (All exception on the network level (timeout, dns errors, etc)
 *     2. ResponseException (All response exceptions)
 *         1. ServerResponseException (All 500 status codes)
 *         2. ClientResponseException (All 400 status codes)
 *         3. RedirectResponseException (All 3000 status codes)
 * ```
 */
class RuntimeException extends \RuntimeException implements HttpClientException
{
    public static function fromPreviousException(Exception $exception): self
    {
        return new self($exception->getMessage(), 0, $exception);
    }

    public static function fromGuzzleException(GuzzleException $exception): self
    {
        return new self($exception->getMessage(), 0, $exception);
    }
}
