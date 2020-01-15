<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception\Http;

/**
 * Exception class to catch all client related http errors (400 range)
 */
class ClientResponseException extends ResponseException
{
    /** @var string */
    protected $supportedStatusCodes = "400:450";
}