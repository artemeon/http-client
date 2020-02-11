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

namespace Artemeon\HttpClient\Client;

use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\Request\Network\ConnectException;
use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

/**
 * Interface to plug in third party http-client libraries
 */
interface HttpClient
{
    /**
     * Sends the request
     *
     * @param Request $request Request object to send
     * @param ClientOptions|null $clientOptions Optional client configuration object
     *
     * @throws HttpClientException  Interface to catch all possible exceptions
     * @throws InvalidArgumentException 1. All exceptions with invalid arguments
     * @throws RuntimeException 2. All exceptions during runtime
     * @throws TransferException 2.1 All exceptions during the request/response transfers
     * @throws ConnectException 2.1.1 All exceptions on the network level like timeouts, etc.
     * @throws ResponseException 2.1.2 All response exceptions
     * @throws ServerResponseException 2.1.2.1 All response exceptions with 500 status codes
     * @throws ClientResponseException 2.1.2.2 All response exceptions with 400 status codes
     * @throws RedirectResponseException 2.1.2.3 All response exceptions with 300 status codes
     * @throws \InvalidArgumentException
     */
    public function send(Request $request, ClientOptions $clientOptions = null): Response;
}
