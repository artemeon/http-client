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

namespace Artemeon\HttpClient\Client\Decorator\Logger;

use Artemeon\HttpClient\Client\Decorator\HttpClientDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * Decorator class to add Psr logging to the httpClient
 */
class LoggerDecorator extends HttpClientDecorator
{
    private LoggerInterface $logger;

    public function __construct(HttpClient $httpClient, LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct($httpClient);
    }

    /**
     * @inheritDoc
     */
    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        try {
            return $this->httpClient->send($request, $clientOptions);
        } catch (ClientResponseException | ServerResponseException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw $exception;
        } catch (HttpClientException $exception) {
            $this->logger->info($exception->getMessage(), ['exception' => $exception]);
            throw $exception;
        }
    }
}
