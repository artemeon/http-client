<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception\Request\Http;

/**
 * Exception class to catch all redirection related http errors (300 range)
 */
class RedirectResponseException extends ResponseException
{
    /** @var string */
    protected $supportedStatusCodes = "300:308";
}