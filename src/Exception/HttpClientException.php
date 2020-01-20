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

/**
 * Base class to catch all possible exceptions related to the http client
 */
class HttpClientException extends Exception
{
    public static function forAlreadyRegisteredHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' is already registered");
    }

    public static function forNonExistentHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' does not exists");
    }
}
