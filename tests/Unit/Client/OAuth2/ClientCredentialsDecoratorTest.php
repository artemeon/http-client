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

namespace Artemeon\HttpClient\Tests\Unit\Client\OAuth2;

use Artemeon\HttpClient\Client\Decorator\OAuth2\ClientCredentials;
use Artemeon\HttpClient\Client\Decorator\OAuth2\ClientCredentialsDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use Artemeon\HttpClient\Stream\Stream;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClientCredentialsDecoratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const string TOKEN_URL = 'https://auth.example.com/oauth2/token';

    private HttpClient & MockInterface $httpClient;

    #[Override]
    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClient::class);
    }

    #[DataProvider('provideJsonContentTypes')]
    public function testAccessTokenIsAcceptedForJsonContentType(string $contentType): void
    {
        /** @var Request|null $request */
        $request = null;
        $this->httpClient->expects('send')->twice()->andReturnUsing(static function (Request $sent) use (&$request, $contentType): Response {
            if ((string) $sent->getUri() === self::TOKEN_URL) {
                return self::tokenResponse($contentType);
            }

            $request = $sent;

            return new Response(200, '1.1');
        });

        $this->createDecorator()->send(Request::forGet(Uri::fromString('https://api.example.com/resource')));

        self::assertInstanceOf(Request::class, $request);
        self::assertSame('Bearer token-4711', $request->getHeaderLine('Authorization'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideJsonContentTypes(): array
    {
        return [
            'plain' => ['application/json'],
            'charset without space' => ['application/json;charset=UTF-8'],
            'charset with space' => ['application/json; charset=utf-8'],
            'upper case' => ['Application/JSON'],
        ];
    }

    #[DataProvider('provideOtherContentTypes')]
    public function testAccessTokenIsRejectedForOtherContentType(?string $contentType): void
    {
        $this->httpClient->expects('send')->once()->andReturn(self::tokenResponse($contentType));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Content type should be: application/json');

        $this->createDecorator()->send(Request::forGet(Uri::fromString('https://api.example.com/resource')));
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function provideOtherContentTypes(): array
    {
        return [
            'html' => ['text/html'],
            'json as prefix of another type' => ['application/jsonp'],
            'missing header' => [null],
        ];
    }

    public function testInvalidStatusCodeIsReportedWithTheStatusCode(): void
    {
        $this->httpClient->expects('send')->once()->andReturn(new Response(401, '1.1', Stream::fromString('invalid_client')));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid status code: 401 for access token request, Body: invalid_client');

        $this->createDecorator()->send(Request::forGet(Uri::fromString('https://api.example.com/resource')));
    }

    public function testClientCredentialsAreSentInTheBodyByDefault(): void
    {
        $tokenRequest = $this->sendAndCaptureTokenRequest(strictRfc: false);

        self::assertSame('grant_type=client_credentials&client_id=client&client_secret=secret', $tokenRequest->getBody()->__toString());
        self::assertFalse($tokenRequest->hasHeader('Authorization'));
    }

    public function testStrictRfcSendsTheClientCredentialsOnlyInTheAuthorizationHeader(): void
    {
        $tokenRequest = $this->sendAndCaptureTokenRequest(strictRfc: true);

        self::assertSame('grant_type=client_credentials', $tokenRequest->getBody()->__toString());
        self::assertSame('Basic ' . base64_encode('client:secret'), $tokenRequest->getHeaderLine('Authorization'));
    }

    public function testFailedAccessTokenRequestIsReportedWithTheCause(): void
    {
        $cause = ServerResponseException::fromResponse(
            new Response(500, '1.1'),
            Request::forGet(Uri::fromString(self::TOKEN_URL)),
            'Internal Server Error',
        );
        $this->httpClient->expects('send')->once()->andThrow($cause);

        try {
            $this->createDecorator()->send(Request::forGet(Uri::fromString('https://api.example.com/resource')));
            self::fail('The failed access token request was not reported.');
        } catch (RuntimeException $exception) {
            self::assertSame('Cant request access token', $exception->getMessage());
            self::assertSame($cause, $exception->getPrevious());
        }
    }

    private function sendAndCaptureTokenRequest(bool $strictRfc): Request
    {
        /** @var Request|null $tokenRequest */
        $tokenRequest = null;
        $this->httpClient->expects('send')->twice()->andReturnUsing(static function (Request $sent) use (&$tokenRequest): Response {
            if ((string) $sent->getUri() !== self::TOKEN_URL) {
                return new Response(200, '1.1');
            }

            $tokenRequest = $sent;

            return self::tokenResponse('application/json');
        });

        $this->createDecorator($strictRfc)->send(Request::forGet(Uri::fromString('https://api.example.com/resource')));

        self::assertInstanceOf(Request::class, $tokenRequest);

        return $tokenRequest;
    }

    private function createDecorator(bool $strictRfc = false): ClientCredentialsDecorator
    {
        return ClientCredentialsDecorator::fromClientCredentials(
            ClientCredentials::forHeaderAuthorization('client', 'secret'),
            Uri::fromString(self::TOKEN_URL),
            $this->httpClient,
            strictRfc: $strictRfc,
        );
    }

    private static function tokenResponse(?string $contentType): Response
    {
        $headers = Headers::create();
        if ($contentType !== null) {
            $headers->add(Header::fromString('Content-Type', $contentType));
        }

        return new Response(
            200,
            '1.1',
            Stream::fromString('{"access_token": "token-4711", "expires_in": 3600, "token_type": "Bearer"}'),
            $headers,
        );
    }
}
