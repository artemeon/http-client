<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception\Request\Http;

use Artemeon\HttpClient\Exception\Request\RequestException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Exception;

/**
 * Exception class to catch all possible http status code ranges
 */
class ResponseException extends RequestException
{
    /** @var Response */
    private $response;

    /** @var int */
    private $statusCode;

    /** @var string */
    protected $supportedStatusCodes = "100:530";

    /**
     * ResponseException constructor.
     */
    protected function __construct(Response $response, Request $request, string $message, Exception $previous = null)
    {
        $this->response = $response;
        $this->statusCode = $response->getStatusCode();

        parent::__construct($request, $message, $previous);
    }

    /**
     * Named constructor to create an instance based on the response of the failed request
     */
    public static function fromResponse(
        Response $response,
        Request $request,
        string $message,
        Exception $previous = null
    ): self {
        return new self($response, $request, $message, $previous);
    }

    /**
     * Returns the range of the supported http status codes
     */
    public function getSupportedStatusCodes(): string
    {
        return $this->supportedStatusCodes;
    }

    /**
     * Returns the Response object
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Returns the http status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
