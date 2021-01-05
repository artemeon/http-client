<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client\Decorator\ClientOptionsModifier;

use Artemeon\HttpClient\Client\Decorator\HttpClientDecorator;
use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Client\Options\ClientOptionsModifier;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

final class HttpClientWithModifiedOptions extends HttpClientDecorator
{
    /** @var ClientOptionsModifier */
    private $clientOptionsModifier;

    public function __construct(HttpClient $httpClient, ClientOptionsModifier $clientOptionsModifier)
    {
        parent::__construct($httpClient);
        $this->clientOptionsModifier = $clientOptionsModifier;
    }

    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        return $this->httpClient->send($request, $this->modified($clientOptions));
    }

    private function modified(?ClientOptions $clientOptions): ClientOptions
    {
        return $this->clientOptionsModifier->modify($clientOptions ?? ClientOptions::fromDefaults());
    }
}
