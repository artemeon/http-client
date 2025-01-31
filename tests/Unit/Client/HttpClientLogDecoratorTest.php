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

namespace Artemeon\HttpClient\Tests\Unit\Client;

use Artemeon\HttpClient\Client\Decorator\Logger\LoggerDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class HttpClientLogDecoratorTest extends TestCase
{
    private LoggerInterface $logger;

    private HttpClient $httpClient;

    private LoggerDecorator $httpClientLogDecorator;

    private ClientOptions $clientOptions;

    /**
     * @inheritDoc
     */
    #[Override]
    protected function setUp(): void
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->clientOptions = ClientOptions::fromDefaults();

        $this->httpClientLogDecorator = new LoggerDecorator(
            $this->httpClient,
            $this->logger,
        );
    }

    public function testSendWillCallDecoratedClass(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(200, '1.1');

        $this->httpClient->shouldReceive('send')->withArgs([$request, $this->clientOptions])
            ->once()
            ->andReturn($response);

        $result = $this->httpClientLogDecorator->send($request, $this->clientOptions);
        self::assertSame($response, $result);
    }

    public function testSendClientThrowsClientResponseExceptionShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(500, '1.1');
        $exception = ClientResponseException::fromResponse($response, $request, 'message');

        $this->httpClient->shouldReceive('send')->withArgs([Mockery::any(), Mockery::any()])->andThrowExceptions([$exception]);
        $this->logger->shouldReceive('error')->withArgs([$exception->getMessage(), ['exception' => $exception]])->once();
        $this->expectException(ClientResponseException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }

    public function testSendClientThrowsServerResponseExceptionShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $response = new Response(500, '1.1');
        $exception = ServerResponseException::fromResponse($response, $request, 'message');

        $this->httpClient->shouldReceive('send')->withArgs([Mockery::any(), Mockery::any()])->andThrowExceptions([$exception]);
        $this->logger->shouldReceive('error')->withArgs([$exception->getMessage(), ['exception' => $exception]])->once();
        $this->expectException(ServerResponseException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }

    public function testSendClientThrowsHttpClientExceptionShouldBeLogged(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache'));
        $exception = InvalidArgumentException::forAlreadyRegisteredHeaderFields('Host');

        $this->httpClient->shouldReceive('send')->withArgs([Mockery::any(), Mockery::any()])->andThrowExceptions([$exception]);
        $this->logger->shouldReceive('info')->withArgs([$exception->getMessage(), ['exception' => $exception]])->once();
        $this->expectException(HttpClientException::class);

        $this->httpClientLogDecorator->send($request, $this->clientOptions);
    }
}
