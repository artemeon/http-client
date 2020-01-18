<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use GuzzleHttp\Client as GuzzleClient;

class HttpClientFactory
{
    public static function create(): HttpClient
    {
        return new ArtemeonHttpClient(
            new GuzzleClient(),
            new ClientOptionsConverter()
        );
    }
}
