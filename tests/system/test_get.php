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
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Url;

use function print_r;

require '../../vendor/autoload.php';

try {
    $request = Request::forGet(Url::fromString('http://www.heise.de'));
    $response = HttpClientFactory::create()->send($request);

    print_r($response->getBody()->getContents());
} catch (HttpClientException $exception) {
    print_r($exception);
}
