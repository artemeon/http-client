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

namespace Artemeon\HttpClient\Client\Decorator\OAuth2;

use Artemeon\HttpClient\Client\Decorator\HttpClientDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\Authorization;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;

/**
 * Http client decorator to add transparent access tokens to requests. Fetches the 'Access Token' from
 * the 'Authorization Server' based on the given Url and ClientCredentials.
 */
class ClientCredentialsDecorator extends HttpClientDecorator
{
    /** @var Request */
    private $accessTokenRequest;

    /** @var AccessToken */
    private $accessToken;

    /**
     * ClientCredentialsDecorator constructor.
     *
     * @param Request $request
     */
    public function __construct(HttpClient $httpClient, Request $request)
    {
        $this->accessTokenRequest = $request;
        $this->accessToken = null;

        parent::__construct($httpClient);
    }

    /**
     * Named constructor to create an instance based on the given ClientCredentials
     *
     * @throws HttpClientException
     */
    public static function fromClientCredentials(
        Uri $url,
        ClientCredentials $clientCredentials,
        HttpClient $httpClient
    ): self {
        $body = Body::fromEncoder(FormUrlEncoder::fromArray($clientCredentials->toArray()));
        $request = Request::forPost($url, $body);

        return new self($httpClient, $request);
    }

    /**
     * @inheritDoc
     */
    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        if (!$this->accessToken instanceof AccessToken) {
            $this->accessToken = $this->requestAccessToken($clientOptions);
        }

        $authorisation = Authorization::forAuthBearer($this->accessToken->getToken());
        $requestWithAuthorisation = $request->withHeader($authorisation->getName(), $authorisation->getValue());

        return $this->httpClient->send($requestWithAuthorisation, $clientOptions);
    }

    /**
     * Fetches the access token
     *
     * @throws RuntimeException
     */
    private function requestAccessToken(ClientOptions $clientOptions = null): AccessToken
    {
        try {
            $response = $this->httpClient->send($this->accessTokenRequest, $clientOptions);
        } catch (HttpClientException $exception) {
            throw new RuntimeException("Cant request access token", 0, $exception);
        }

        $this->assertIsValidJsonResponse($response);

        return AccessToken::fromJsonString($response->getBody()->__toString());
    }

    /**
     * Checks for a valid access token response with json body
     *
     * @throws RuntimeException
     */
    private function assertIsValidJsonResponse(Response $response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                sprintf(
                    "Invalid status code: s% for access token request, Body: %s",
                    $response->getStatusCode(),
                    $response->getBody()->__toString()
                )
            );
        }

        // According RFC header fields are case incentive, normalize to lower for comparison
        $contentType = $response->getHeader(HeaderField::CONTENT_TYPE);
        $contentType = array_map('strtolower', $contentType);

        if (!in_array(MediaType::JSON, $contentType)) {
            throw new RuntimeException('Content type should be: ' . MediaType::JSON);
        }
    }
}
