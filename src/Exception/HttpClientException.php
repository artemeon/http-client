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

use RuntimeException;

/**
 * Base class to catch all possible exceptions related to the http client
 *
 * ```
 * 1. HttpClientException (All possible exceptions inclusive during instantiation)
 *   1. TransferException (All exception during request/response transfer)
 *     1. ConnectException (All exception on the network level (timeout, dns errors, etc)
 *     2. ResponseException (All response exceptions)
 *         1. ServerResponseException (All 500 status codes)
 *         2. ClientResponseException (All 400 status codes)
 *         3. RedirectResponseException (All 3000 status codes)
 * ```
 */
class HttpClientException extends RuntimeException
{
    public static function forAlreadyRegisteredHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' is already registered");
    }

    public static function forNonExistentHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' does not exists");
    }

    public static function fromGuzzleException(RuntimeException $exception): self
    {
        return new self($exception->getMessage(), 0, $exception);
    }
}
