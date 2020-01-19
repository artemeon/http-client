<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client;

use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

interface HttpClient
{
    public function send(Request $request, ClientOptions $clientOptions = null): Response;
}
