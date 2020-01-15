<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

$request = Request::forGet(
    Url::withQueryParams('http://test.de', ["pager" => 5])
);

$client = new GuzzleHttpClient();
$client->send($request);