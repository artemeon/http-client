<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Model\ClientOptions;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Response;

class GuzzleHttpClient implements HttpClient
{

    public function send(Request $request, ClientOptions $requestOptions = null): Response
    {
        // TODO: Implement sendRequest() method.
    }
}
