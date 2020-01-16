<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

use function print_r;

try {
    $request = Request::forPost(
        Url::fromString('http://test.de'),
        Body::forUrlEncodedFormData(["test" => 2342])
    );

    $client = new GuzzleHttpClient();
    $response = $client->send($request);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}



