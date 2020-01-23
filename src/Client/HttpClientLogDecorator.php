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

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * Decorator class to add logging to the httpClient
 */
class HttpClientLogDecorator implements HttpClient
{
    /** @var LoggerInterface */
    private $logger;

    /** @var HttpClient */
    private $httpClient;

    /**
     * HttpClientLogDecorator constructor.
     *
     * @param LoggerInterface $logger
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
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
