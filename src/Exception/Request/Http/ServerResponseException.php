<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception\Request\Http;

/**
 * Exception class to catch all server related http errors (500 range)
 */
class ServerResponseException extends ResponseException
{
    /** @var string */
    protected $supportedStatusCodes = "500:511";
}