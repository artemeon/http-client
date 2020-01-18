<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Response;

interface HttpClient
{
    public function send(Request $request, ClientOptions $clientOptions = null): Response;
}
