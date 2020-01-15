<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception;

use Artemeon\HttpClient\Model\Request;
use Exception;

/**
 * Base class to catch all possible exceptions during the request
 */
class HttpClientException extends Exception
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
     * Named constuctor to create an instance based on the given request object
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
