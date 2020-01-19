<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\Authorisation;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Url;
use Artemeon\HttpClient\Client\ClientOptions;
use Artemeon\HttpClient\Client\HttpClientFactory;

use function print_r;

try {
    $request = Request::forPost(
        Url::fromString('http://test.de'),
        Body::fromEncoder(FormUrlEncoder::fromArray(["test" => 2342])),
        Headers::fromFields([
            Authorisation::forAuthBasic('John.Doe', 'geheim'),
            UserAgent::fromString()
        ])
    );

    $clientOptions = ClientOptions::fromDefaults();
    $clientOptions->optDisableCertificateVerification();
    $clientOptions->optSetTimeout(15);

    $response = HttpClientFactory::create()->send($request, $clientOptions);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}
