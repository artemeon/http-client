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

use Artemeon\HttpClient\Client\ArtemeonHttpClient;
use Artemeon\HttpClient\Client\ClientOptions;
use Artemeon\HttpClient\Client\ClientOptionsConverter;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\Request\Network\ConnectException;
use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Url;
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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;

/**
 * @covers \Artemeon\HttpClient\Client\ArtemeonHttpClient
 * @covers \Artemeon\HttpClient\Exception\HttpClientException
 * @covers \Artemeon\HttpClient\Exception\Request\TransferException
 * @covers \Artemeon\HttpClient\Exception\Request\Network\ConnectException
 * @covers \Artemeon\HttpClient\Exception\Request\Http\ResponseException
 * @covers \Artemeon\HttpClient\Exception\Request\Http\ServerResponseException
 * @covers \Artemeon\HttpClient\Exception\Request\Http\ClientResponseException
 * @covers \Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException
 */
class ArtemeonHttpClientTest extends TestCase
{
    /** @var GuzzleClient */
    private $guzzleClient;

    /** @var MockHandler */
    private $mockHandler;

    /** @var ArtemeonHttpClient */
    private $httpClient;

    /** @var ClientOptions */
    private $clientOptions;

    /** @var ClientOptionsConverter */
    private $clientOptionsConverter;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->guzzleClient = new GuzzleClient(['handler' => HandlerStack::create($this->mockHandler)]);
        $this->clientOptionsConverter = $this->prophesize(ClientOptionsConverter::class);
        $this->clientOptions = ClientOptions::fromDefaults();

        $this->httpClient = new ArtemeonHttpClient(
            $this->guzzleClient,
            $this->clientOptionsConverter->reveal()
        );
    }

    /**
     * @test
     */
    public function send_WithoutOptions_UsesEmptyOptionsArray()
    {
        $this->mockHandler->append(new GuzzleResponse(200, [], 'Some body content'));
        $this->clientOptionsConverter->toGuzzleOptionsArray(Argument::any())->shouldNotBeCalled();

        $request = Request::forGet(Url::fromString('http://apache/'));
        $response = $this->httpClient->send($request);

        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     */
    public function send_WithOptions_ConvertOptions()
    {
        $this->mockHandler->append(new GuzzleResponse(200, [], 'Some body content'));
        $this->clientOptionsConverter->toGuzzleOptionsArray($this->clientOptions)
            ->shouldBeCalled()
            ->willReturn([]);

        $request = Request::forGet(Url::fromString('http://apache/'));
        $response = $this->httpClient->send($request, $this->clientOptions);

        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     */
    public function send_ConvertsRequestToValidGuzzleRequest(): void
    {
        $request = Request::forPost(
            Url::fromString('http://apache/endpoints/upload.php'),
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            Headers::fromFields([UserAgent::fromString()])
        );

        $this->mockHandler->append(new GuzzleResponse(200, [], 'Some body content'));
        $this->httpClient->send($request);
        $guzzleRequest = $this->mockHandler->getLastRequest();

        self::assertSame($request->getMethod(), $guzzleRequest->getMethod());
        self::assertSame($request->getUrl()->__toString(), $guzzleRequest->getUri()->__toString());
        self::assertSame($request->getHeaders(), $guzzleRequest->getHeaders());
        self::assertSame($request->getBody()->__toString(), $guzzleRequest->getBody()->__toString());
        self::assertSame($request->getProtocolVersion(), $guzzleRequest->getProtocolVersion());
    }

    /**
     * @test
     */
    public function send_ConvertsGuzzleResponseToValidResponse(): void
    {
        $request = Request::forGet(Url::fromString('http://apache/endpoints/upload.php'));
        $expectedContent = 'Some body content';
        $expectedHeaders = ['Content-Type' => ['text/plain']];
        $expectedStatusCode = 200;

        $this->mockHandler->append(new GuzzleResponse($expectedStatusCode, $expectedHeaders, $expectedContent));
        $response = $this->httpClient->send($request);

        self::assertSame($expectedStatusCode, $response->getStatusCode());
        self::assertSame($expectedHeaders, $response->getHeaders());
    }

    /**
     * @test
     * @dataProvider provideExceptionMappings
     */
    public function send_GuzzleThrowsException_MappedToHttpClientException(
        RuntimeException $guzzleException,
        string $httpClientException
    ) {
        $this->mockHandler->append($guzzleException);
        $request = Request::forGet(Url::fromString('http://apache/endpoints/upload.php'));

        $this->expectException($httpClientException);
        $this->httpClient->send($request);
    }

    /**
     * Data provider for exception mappings from guzzle to httpClient exceptions
     */
    public function provideExceptionMappings(): array
    {
        $fakeResponse = new GuzzleResponse(200);
        $fakeRequest = new GuzzleRequest('GET', 'test');

        return [
            [
                new GuzzleClientException('Shit happens', $fakeRequest, $fakeResponse),
                ClientResponseException::class
            ],
            [
                new GuzzleServerException('Shit happens', $fakeRequest, $fakeResponse),
                ServerResponseException::class
            ],
            [
                new GuzzleBadResponseException('Shit happens', $fakeRequest, $fakeResponse),
                ResponseException::class
            ],
            [
                new GuzzleConnectException('Shit happens', $fakeRequest),
                ConnectException::class
            ],
            [
                new GuzzleTooManyRedirectsException('Shit happens', $fakeRequest, $fakeResponse),
                RedirectResponseException::class
            ],
            [
                new GuzzleRequestException('Shit happens', $fakeRequest, $fakeResponse),
                ResponseException::class
            ],
            [
                new GuzzleTransferException('Shit happens'),
                TransferException::class
            ],
            [
                new RuntimeException('Shit happens'),
                HttpClientException::class
            ],
        ];
    }
}
