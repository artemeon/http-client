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
use Artemeon\HttpClient\Http\Body\Reader\FileReader;
use Artemeon\HttpClient\Http\Header\Fields\Authorization;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use GuzzleHttp\MessageFormatter;

require '../../vendor/autoload.php';

$transactions = [];
$formatter = new MessageFormatter(MessageFormatter::DEBUG);

try {
    $request = Request::forPost(
        Uri::fromString('http://apache/endpoints/upload.php'),
        Body::fromReader(FileReader::fromFile('../fixtures/encoder/generated.json')),
        Headers::fromFields([Authorization::forAuthBasic('John.Doe', 'geheim')])
    );

    HttpClientFactory::withTransactionMiddleware($transactions)->send($request);

    echo nl2br($formatter->format($transactions[0]['request'], $transactions[0]['response']));
} catch (HttpClientException $exception) {
    print_r($exception);
}
