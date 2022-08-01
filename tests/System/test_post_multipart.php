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

use Artemeon\HttpClient\Client\HttpClientTestFactory;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\MultipartFormDataEncoder;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use Artemeon\HttpClient\Stream\Stream;

require '../../vendor/autoload.php';

try {
    $request = Request::forPost(
        Uri::fromString('http://apache/endpoints/upload.php'),
        Body::fromEncoder(
            MultipartFormDataEncoder::create()
                ->addFieldPart('user', 'John.Doe')
                ->addFieldPart('password', utf8_encode('geheim'))
                ->addFilePart('user_image', 'header_logo.png', Stream::fromFile('../Fixtures/reader/header_logo.png'))
        )
    );

    HttpClientTestFactory::withTransactionLog()->send($request);
    HttpClientTestFactory::printTransactionLog();
} catch (HttpClientException $exception) {
    print_r($exception);
}

