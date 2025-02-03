<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception;

/**
 * Exception class for all invalid argument exceptions.
 */
class InvalidArgumentException extends \InvalidArgumentException implements HttpClientException
{
    /**
     * @param string $fieldName Already existent field name
     */
    public static function forAlreadyRegisteredHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' is already registered");
    }

    /**
     * @param string $fieldName Non existent field name
     */
    public static function forNonExistentHeaderFields(string $fieldName): self
    {
        return new self("Header field '$fieldName' does not exists");
    }
}
