<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Url;
use Artemeon\HttpClient\Client\HttpClientFactory;

use function print_r;

require '../../vendor/autoload.php';

try {
    $request = Request::forGet(Url::fromString('http://www.heise.de'));
    $response = HttpClientFactory::create()->send($request);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}
