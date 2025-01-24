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

use Artemeon\HttpClient\Client\ArtemeonHttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Client\Options\ClientOptionsConverter;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\Request\Network\ConnectException;
use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Exception\TooManyRedirectsException as GuzzleTooManyRedirectsException;
use GuzzleHttp\Exception\TransferException as GuzzleTransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @internal
 */
#[CoversClass(ArtemeonHttpClient::class)]
#[CoversClass(RuntimeException::class)]
#[CoversClass(TransferException::class)]
#[CoversClass(ConnectException::class)]
#[CoversClass(ResponseException::class)]
#[CoversClass(ServerResponseException::class)]
#[CoversClass(ClientResponseException::class)]
#[CoversClass(RedirectResponseException::class)]
class ArtemeonHttpClientTest extends TestCase
{
    use ProphecyTrait;

    private GuzzleClient $guzzleClient;
    private MockHandler $mockHandler;
    private ArtemeonHttpClient $httpClient;
    private ClientOptions $clientOptions;
    private ClientOptionsConverter | ObjectProphecy $clientOptionsConverter;

    /**
     * @inheritDoc
     */
    #[Override]
    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($this->mockHandler)]);
        $this->clientOptionsConverter = $this->prophesize(ClientOptionsConverter::class);
        $this->clientOptions = ClientOptions::fromDefaults();

        $this->httpClient = new ArtemeonHttpClient(
            $this->guzzleClient,
            $this->clientOptionsConverter->reveal(),
        );
    }

    #[Test]
    public function sendWithoutOptionsUsesEmptyOptionsArray(): void
    {
        $this->mockHandler->append(new GuzzleResponse(200, [], 'Some body content'));
        $this->clientOptionsConverter->toGuzzleOptionsArray(Argument::any())->shouldNotBeCalled();

        $request = Request::forGet(Uri::fromString('http://apache/'));
        $response = $this->httpClient->send($request);

        self::assertInstanceOf(Response::class, $response);
    }

    #[Test]
    public function sendWithOptionsConvertOptions(): void
    {
        $this->mockHandler->append(new GuzzleResponse(200, [], 'Some body content'));
        $this->clientOptionsConverter->toGuzzleOptionsArray($this->clientOptions)
            ->shouldBeCalled()
            ->willReturn([]);

        $request = Request::forGet(Uri::fromString('http://apache/'));
        $response = $this->httpClient->send($request, $this->clientOptions);

        self::assertInstanceOf(Response::class, $response);
    }

    #[Test]
    public function sendConvertsGuzzleResponseToValidResponse(): void
    {
        $request = Request::forGet(Uri::fromString('http://apache/endpoints/upload.php'));
        $expectedContent = 'Some body content';
        $expectedHeaders = ['Content-Type' => ['text/plain']];
        $expectedStatusCode = 200;

        $this->mockHandler->append(new GuzzleResponse($expectedStatusCode, $expectedHeaders, $expectedContent));
        $response = $this->httpClient->send($request);

        self::assertSame($expectedStatusCode, $response->getStatusCode());
        self::assertSame($expectedHeaders, $response->getHeaders());
    }

    #[DataProvider('provideExceptionMappings')]
    #[Test]
    public function sendGuzzleThrowsExceptionMappedToHttpClientException(
        \RuntimeException $guzzleException,
        string $httpClientException,
    ): void {
        $this->mockHandler->append($guzzleException);
        $request = Request::forGet(Uri::fromString('http://apache/endpoints/upload.php'));

        $this->expectException($httpClientException);
        $this->httpClient->send($request);
    }

    /**
     * Data provider for exception mappings from guzzle to httpClient exceptions.
     */
    public function provideExceptionMappings(): array
    {
        $fakeResponse = new GuzzleResponse(200);
        $fakeRequest = new GuzzleRequest('GET', 'test');

        return [
            [
                new GuzzleClientException('Shit happens', $fakeRequest, $fakeResponse),
                ClientResponseException::class,
            ],
            [
                new GuzzleServerException('Shit happens', $fakeRequest, $fakeResponse),
                ServerResponseException::class,
            ],
            [
                new GuzzleBadResponseException('Shit happens', $fakeRequest, $fakeResponse),
                ResponseException::class,
            ],
            [
                new GuzzleConnectException('Shit happens', $fakeRequest),
                ConnectException::class,
            ],
            [
                new GuzzleTooManyRedirectsException('Shit happens', $fakeRequest, $fakeResponse),
                RedirectResponseException::class,
            ],
            [
                new GuzzleRequestException('Shit happens', $fakeRequest, $fakeResponse),
                ResponseException::class,
            ],
            [
                new GuzzleTransferException('Shit happens'),
                TransferException::class,
            ],
            [
                new \RuntimeException('Shit happens'),
                RuntimeException::class,
            ],
        ];
    }
}
