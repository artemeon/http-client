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

namespace Artemeon\HttpClient\Exception\Request;

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Request;
use Exception;
use Throwable;

/**
 * Class for all runtime exceptions during the request/response transfers
 */
class TransferException extends RuntimeException
{
    protected Request $request;

    /**
     * Named constructor to create an instance based on the given request object
     *
     * @param Request $request The failed request object
     * @param string $message The error message
     * @param Exception|null $previous The precious third party exception
     */
    public static function fromRequest(Request $request, string $message, ?Exception $previous = null): static
    {
        $instance = new static($message, 0, $previous);
        $instance->request = $request;

        return $instance;
    }

    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the request object of the failed request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
