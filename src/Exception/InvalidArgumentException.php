<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements HttpClientException
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
