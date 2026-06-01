<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client\Decorator\ClientOptionsModifier;

use Artemeon\HttpClient\Client\Decorator\HttpClientDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Client\Options\ClientOptionsModifier;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpClientWithModifiedOptions extends HttpClientDecorator
{
    public function __construct(HttpClient $httpClient, private readonly ClientOptionsModifier $clientOptionsModifier)
    {
        parent::__construct($httpClient);
    }

    #[Override]
    public function send(RequestInterface $request, ?ClientOptions $clientOptions = null): ResponseInterface
    {
        return $this->httpClient->send($request, $this->modified($clientOptions));
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->send($request);
    }

    private function modified(?ClientOptions $clientOptions): ClientOptions
    {
        return $this->clientOptionsModifier->modify($clientOptions ?? ClientOptions::fromDefaults());
    }
}
