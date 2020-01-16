<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

use function print_r;

try {
    $request = Request::forGet(Url::withQueryParams('http://test.de', ["pager" => 5]));

    $client = new GuzzleHttpClient();
    $response = $client->send($request);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}
