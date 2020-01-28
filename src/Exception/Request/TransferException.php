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

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Request;
use Exception;

class TransferException extends HttpClientException
{
    /** @var Request */
    protected $request;

    /**
     * Named constructor to create an instance based on the given request object
     */
    public static function fromRequest(Request $request, string $message, Exception $previous = null): self
    {
        $instance = new static($message, 0, $previous);
        $instance->request = $request;

        return $instance;
    }

    /**
     * Returns the request object of the failed request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
