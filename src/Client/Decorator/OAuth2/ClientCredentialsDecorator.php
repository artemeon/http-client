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
use Artemeon\HttpClient\Client\Decorator\OAuth2\Token\AccessToken;
use Artemeon\HttpClient\Client\Decorator\OAuth2\Token\AccessTokenCache;
use Artemeon\HttpClient\Client\Decorator\OAuth2\Token\InMemoryAccessTokenCache;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\Authorization;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use Exception;

/**
 * Http client decorator to add transparent access tokens to requests. Fetches the 'Access Token' from
 * the 'Authorization Server' based on the given Url and ClientCredentials.
 */
class ClientCredentialsDecorator extends HttpClientDecorator
{
    /** @var Request */
    private $accessTokenRequest;

    /** @var AccessTokenCache */
    private $accessTokenCache;

    /**
     * ClientCredentialsDecorator constructor.
     *
     * @param HttpClient $httpClient The http client to decorate
     * @param Request $accessTokenRequest The http request object
     * @param AccessTokenCache $accessTokenCache Cache strategy to store the access token
     */
    public function __construct(
        HttpClient $httpClient,
        Request $accessTokenRequest,
        AccessTokenCache $accessTokenCache
    ) {
        $this->accessTokenRequest = $accessTokenRequest;
        $this->accessTokenCache = $accessTokenCache;

        parent::__construct($httpClient);
    }

    /**
     * Named constructor to create an instance based on the given ClientCredentials
     *
     * @param ClientCredentials $clientCredentials The OAuth2 client credential object
     * @param Uri $uri The Uri object
     * @param HttpClient $httpClient The http client to decorate
     * @param AccessTokenCache|null $accessTokenCache AccessTokenCache implementation
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function fromClientCredentials(
        ClientCredentials $clientCredentials,
        Uri $uri,
        HttpClient $httpClient,
        AccessTokenCache $accessTokenCache = null
    ): self {
        // Ensure default cache strategy
        if ($accessTokenCache === null) {
            $accessTokenCache = new InMemoryAccessTokenCache();
        }

        $body = Body::fromEncoder(FormUrlEncoder::fromArray($clientCredentials->toArray()));
        $accessTokenRequest = Request::forPost($uri, $body);

        return new self($httpClient, $accessTokenRequest, $accessTokenCache);
    }

    /**
     * @inheritDoc
     */
    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        if ($this->accessTokenCache->isExpired()) {
            $this->accessTokenCache->add($this->requestAccessToken());
        }

        $accessToken = $this->accessTokenCache->get();
        $authorisation = Authorization::forAuthBearer($accessToken->getToken());
        $requestWithAuthorisation = $request->withHeader($authorisation->getName(), $authorisation->getValue());

        return $this->httpClient->send($requestWithAuthorisation, $clientOptions);
    }

    /**
     * Fetches the access token
     *
     * @param ClientOptions|null $clientOptions
     * @throws RuntimeException
     */
    private function requestAccessToken(ClientOptions $clientOptions = null): AccessToken
    {
        try {
            $response = $this->httpClient->send($this->accessTokenRequest, $clientOptions);
        } catch (HttpClientException | Exception $exception) {
            throw new RuntimeException("Cant request access token", 0, $exception);
        }

        $this->assertIsValidJsonResponse($response);

        return AccessToken::fromJsonString($response->getBody()->__toString());
    }

    /**
     * Checks for a valid access token response with valid json body
     *
     * @param Response $response
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
