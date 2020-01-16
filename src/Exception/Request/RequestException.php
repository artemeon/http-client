<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception\Request;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Request;
use Exception;

class RequestException extends HttpClientException
{
    /** @var Request */
    private $request;

    /**
     * HttpClientException constructor.
     */
    protected function __construct(Request $request, string $message, Exception $previous = null)
    {
        $this->request = $request;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Named constructor to create an instance based on the given request object
     */
    public static function fromRequest(Request $request, string $message, Exception $previous = null): self
    {
        return new self($request, $message, $previous);
    }

    /**
     * Returns the request object of the failed request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}