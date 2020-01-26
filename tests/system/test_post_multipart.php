<?php

/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Client\HttpClientFactory;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\MultipartFormDataEncoder;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Url;
use Artemeon\HttpClient\Stream\Stream;
use GuzzleHttp\MessageFormatter;

require '../../vendor/autoload.php';

$transactions = [];
$formatter = new MessageFormatter('{request}');

try {
    $request = Request::forPost(
        Url::fromString('http://apache/endpoints/upload.php'),
        Body::fromEncoder(
            MultipartFormDataEncoder::create()
                ->addFieldPart('user', 'John.Doe')
                ->addFieldPart('password', utf8_encode('geheim'))
                ->addFilePart('user_image', 'header_logo.png', Stream::fromFile('../fixtures/reader/header_logo.png'))
        )
    );

    $response = HttpClientFactory::withMiddleware($transactions)->send($request);

    echo nl2br($formatter->format($transactions[0]['request']));
    echo nl2br($response->getBody()->__toString());
} catch (HttpClientException $exception) {
    print_r($exception);
}

