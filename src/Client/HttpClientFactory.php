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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;

/**
 * Static factory class
 */
class HttpClientFactory
{
    /**
     * Named constructor to create an instance for production environments without logging
     */
    public static function create(): HttpClient
    {
        return new ArtemeonHttpClient(
            new GuzzleClient(),
            new ClientOptionsConverter()
        );
    }

    /**
     * Named constructor to create an instance for production with a PSR 3 logger for
     * request/response calls and all occurred exceptions
     *
     * @param LoggerInterface $logger PSR-3 logger @see https://www.php-fig.org/psr/psr-3/
     * @param string $format @see \GuzzleHttp\MessageFormatter for all allowed options
     */
    public static function withLogger(LoggerInterface $logger, string $format='{request} - {ressponse}'): HttpClient
    {
        $formatter =  new MessageFormatter($format);
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::log($logger, $formatter));

        $httpClient = new ArtemeonHttpClient(
            new GuzzleClient(['handler' => $handlerStack]),
            new ClientOptionsConverter()
        );

        return new HttpClientLogDecorator($httpClient, $logger);
    }

    /**
     * Named constructor to create an instance with a middleware to record transactions only for debugging purposes
     *
     * Example:
     * $transactions = []
     * HttpClientFactory::withMiddleware($transactions)->send($request);
     *
     * print_r($transactions[0]['request']);
     * print_r($transactions[0]['response']);
     * echo $transactions[0]['request']->getBody();
     */
    public static function withTransactionMiddleware(array &$transactions): HttpClient
    {
        $history = Middleware::history($transactions);
        $handlerStack = HandlerStack::create();
        $handlerStack->push($history);

        return new ArtemeonHttpClient(
            new GuzzleClient(['handler' => $handlerStack]),
            new ClientOptionsConverter()
        );
    }
}
