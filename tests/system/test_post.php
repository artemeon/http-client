<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

$request = Request::forPost(
    Url::fromString('http://test.de'),
    Body::forUrlEncodedFormData(["test" => 2342])
);

$client = new GuzzleHttpClient();
$client->send($request);
