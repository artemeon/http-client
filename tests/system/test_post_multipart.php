<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\Body\Encoder\MultipartFormDataEncoder;
use Artemeon\HttpClient\Model\Header\Fields\Authorisation;
use Artemeon\HttpClient\Model\Header\Headers;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Url;
use Artemeon\HttpClient\Service\HttpClientFactory;

use function print_r;

try {
    $request = Request::forPost(
        Url::fromString('http://test.de'),
        Body::fromEncoder(
            MultipartFormDataEncoder::create()
                ->addFieldPart('test', 'dfgdfgd')
                ->addFilePart('gdfdf', 'file.txt', 'sdfsdfsdfs')
        ),
        Headers::fromFields([Authorisation::forAuthBasic('John.Doe', 'geheim')])
    );

    $response = HttpClientFactory::create()->send($request);

    print_r($response);
} catch (HttpClientException $exception) {
    print_r($exception);
}
