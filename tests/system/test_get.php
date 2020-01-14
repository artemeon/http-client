<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

$httpClient = new GuzzleHttpClient();
$httpClient->send(Request::forGet('http://test.de'));