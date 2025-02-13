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

namespace Artemeon\HttpClient\Exception\Request\Http;

use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Exception;

/**
 * Exception class to catch all possible http status code ranges.
 */
class ResponseException extends TransferException
{
    protected ?Response $response = null;
    protected int $statusCode;

    /**
     * Named constructor to create an instance based on the response of the failed request.
     *
     * @param ?Response $response The failed response if exists
     * @param Request $request The failed request
     * @param string $message The error message
     * @param Exception|null $previous The previous exception
     */
    public static function fromResponse(
        ?Response $response,
        Request $request,
        string $message,
        ?Exception $previous = null,
    ): static {
        $instance = new static($message, 0, $previous);
        $instance->request = $request;
        $instance->response = $response;
        $instance->statusCode = $response?->getStatusCode() ?? 0;

        return $instance;
    }

    /**
     * Returns the Response object.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Checks if we have a response object.
     */
    public function hasResponse(): bool
    {
        return $this->response instanceof Response;
    }

    /**
     * Returns the http status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
