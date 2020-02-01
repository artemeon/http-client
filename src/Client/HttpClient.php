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
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\Request\Network\ConnectException;
use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

interface HttpClient
{
    /**
     * Sends the request
     *
     * @throws HttpClientException
     * @throws RuntimeException
     * @throws TransferException
     * @throws ConnectException
     * @throws ResponseException
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws RedirectResponseException
     */
    public function send(Request $request, ClientOptions $clientOptions = null): Response;
}
