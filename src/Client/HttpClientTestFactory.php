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

use Artemeon\HttpClient\Client\Options\ClientOptionsConverter;
use Artemeon\HttpClient\Exception\RuntimeException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Static factory class only for testing and mocking cases!
 */
class HttpClientTestFactory
{
    /** @var array */
    private $transactionLog = [];

    /** @var MockHandler */
    private $mockHandler;

    /** @var HttpClientTestFactory */
    private static $instance;

    /**
     * HttpClientMockFactory constructor.
     */
    public function __construct()
    {
        $this->transactionLog = [];
        $this->mockHandler = new MockHandler();
    }

    /**
     * Singleton factory
     */
    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Named constructor to create an instance with a middleware to record transactions only for debugging purposes
     *
     * Example:
     * $httpClient = HttpClientTestFactory::withTransactionLog();
     * print_r(HttpClientTestFactory::getTransactionLog);
     */
    public static function withTransactionLog(): HttpClient
    {
        try {
            $instance = self::getInstance();
            $history = Middleware::history($instance->transactionLog);
            $handlerStack = HandlerStack::create();
            $handlerStack->push($history);

            return new ArtemeonHttpClient(
                new GuzzleClient(['handler' => $handlerStack]),
                new ClientOptionsConverter()
            );
        } catch (InvalidArgumentException $exception) {
            throw RuntimeException::fromGuzzleException($exception);
        }
    }

    /**
     * Creates an instance to mock
     *
     * ```php
     * HttpClientTestFactory::mockResponses(
     *   [
     *     new Response(200, '1.1', Stream::fromString('Response 1')),
     *     new Response(200, '1.1', Stream::fromString('Response 2')),
     *   ]
     * );
     *
     * $client = HttpClientTestFactory::withMockHandler();
     * $response_1 = $client->send(Request::forGet(Uri::fromString('www.artemeon.de')));
     * $response_2 = $client->send(Request::forGet(Uri::fromString('www.artemeon.de')));
     * ```
     */
    public static function withMockHandler(): HttpClient
    {
        try {
            $instance = self::getInstance();

            return new ArtemeonHttpClient(
                new GuzzleClient(['handler' => HandlerStack::create($instance->mockHandler)]),
                new ClientOptionsConverter()
            );
        } catch (InvalidArgumentException $exception) {
            throw RuntimeException::fromGuzzleException($exception);
        }
    }

    /**
     * Register the responses to mock
     *
     * @param array $responses
     * @throws InvalidArgumentException
     */
    public static function mockResponses(array $responses): void
    {
        $instance = self::getInstance();

        foreach ($responses as $response) {
            if (!$response instanceof ResponseInterface) {
                throw new InvalidArgumentException('PSR-7 Response required');
            }

            $instance->mockHandler->append($response);
        }
    }

    /**
     * Return the recorded transaction log array
     */
    public static function getTransactionLog(): array
    {
        return self::getInstance()->transactionLog;
    }

    /**
     * Returns the formatted transaction logs as a string
     */
    public static function printTransactionLog(): void
    {
        $result = '';
        $formatter = new MessageFormatter(MessageFormatter::DEBUG);

        foreach (self::getTransactionLog() as $transaction) {
            $result = nl2br($formatter->format($transaction['request'], $transaction['response']));
        }

        echo $result;
    }
}
