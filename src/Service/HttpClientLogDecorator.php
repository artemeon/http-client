<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Model\ClientOptions;
use Artemeon\HttpClient\Model\Request;

class HttpClientLogDecorator implements HttpClient
{
    public function send(Request $request, ClientOptions $requestOptions = null)
    {
        //void
    }
}