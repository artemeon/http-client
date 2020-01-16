<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Model\ClientOptions;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Response;

class HttpClientLogDecorator implements HttpClient
{

    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        //void
    }
}