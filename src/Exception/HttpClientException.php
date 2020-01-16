<?php

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
}
