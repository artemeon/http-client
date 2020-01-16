<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Authorisation;
use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\ClientOptions;
use Artemeon\HttpClient\Model\Header\Header;
use Artemeon\HttpClient\Model\Header\Headers;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

use function print_r;

try {
    $headers = new Headers();
    $headers->addHeader(Header::forAuthorisation(Authorisation::forAuthBasic('John.Doe', 'geheim')));
    $headers->addHeader(Header::forUserAgent());

    $request = Request::forPost(
        Url::fromString('http://test.de'),
        Body::forUrlEncodedFormData(["test" => 2342]),
        $headers
    );

    $clientOptions = ClientOptions::fromDefaults();
    $clientOptions->disableCertificateVerification();

    $httpClient = new GuzzleHttpClient();
    $response = $httpClient->send($request, $clientOptions);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}
