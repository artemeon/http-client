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

namespace Artemeon\HttpClient\Tests\Client;

use Artemeon\HttpClient\Client\Decorator\Logger\LoggerDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @covers \Artemeon\HttpClient\Client\Decorator\Logger\LoggerDecorator
 */
class HttpClientLogDecoratorTest extends TestCase
{
    /** @var LoggerInterface */
    private $logger;

    /** @var HttpClient */
    private $httpClient;

    /** @var \Artemeon\HttpClient\Client\Decorator\Logger\LoggerDecorator */
    private $httpClientLogDecorator;

    /** @var \Artemeon\HttpClient\Client\Options\ClientOptions */
    private $clientOptions;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->httpClient = $this->prophesize(HttpClient::class);
        $this->clientOptions = \Artemeon\HttpClient\Client\Options\ClientOptions::fromDefaults();

        $this->httpClientLogDecorator = new LoggerDecorator(
            $this->httpClient->reveal(),
            $this->logger->reveal()
        );
    }

    /**
     * @test
     */
    public function send_WillCallDecoratedClass(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(200, '1.1');

        $this->httpClient->send($request, $this->clientOptions)
            ->willReturn($response)
            ->shouldBeCalled();

        $result = $this->httpClientLogDecorator->send($request, $this->clientOptions);
        self::assertSame($response, $result);
    }

    /**
     * @test
     */
    public function send_ClientThrowsClientResponseException_ShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(500, '1.1');
        $exception = ClientResponseException::fromResponse($response, $request, 'message');

        $this->httpClient->send(Argument::any(), Argument::any())->willThrow($exception);
        $this->logger->error($exception->getMessage(), ['exception' => $exception])->shouldBeCalled();
        $this->expectException(ClientResponseException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }

    /**
     * @test
     */
    public function send_ClientThrowsServerResponseException_ShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(500, '1.1');
        $exception = ServerResponseException::fromResponse($response, $request, 'message');

        $this->httpClient->send(Argument::any(), Argument::any())->willThrow($exception);
        $this->logger->error($exception->getMessage(), ['exception' => $exception])->shouldBeCalled();
        $this->expectException(ServerResponseException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }

    /**
     * @test
     */
    public function send_ClientThrowsHttpClientException_ShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $exception = RuntimeException::forAlreadyRegisteredHeaderFields('Host');

        $this->httpClient->send(Argument::any(), Argument::any())->willThrow($exception);
        $this->logger->info($exception->getMessage(), ['exception' => $exception])->shouldBeCalled();
        $this->expectException(RuntimeException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }
}
