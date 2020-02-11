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

use Artemeon\HttpClient\Client\Decorator\OAuth2\ClientCredentials;
use Artemeon\HttpClient\Client\Decorator\OAuth2\ClientCredentialsDecorator;
use Artemeon\HttpClient\Client\HttpClientTestFactory;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Header\Fields\ContentType;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Http\Uri;
use Artemeon\HttpClient\Stream\Stream;

require '../../vendor/autoload.php';

HttpClientTestFactory::mockResponses(
    [
        new Response(
            200,
            '1.1',
            Stream::fromString(
                '{"access_token": "PQtdWwDDESjpSyYnDAerj92O3sHWlZ", "expires_in": 7884000, "token_type": "Bearer", "scope": "read_suppliers"}'
            ),
            Headers::fromFields([ContentType::fromString(MediaType::JSON)])
        ),
        new Response(200, '1.1', Stream::fromString('It works')),
    ]
);

try {
    $apiClient = ClientCredentialsDecorator::fromClientCredentials(
        ClientCredentials::fromHeaderAuthorization(
            'yoour_client_id',
            'your_client_secret',
            'read_suppliers'
        ),
        Uri::fromString('https://api.lbbw-test.prospeum.com/o/token/'),
        HttpClientTestFactory::withMockHandler()
    );

    $response = $apiClient->send(
        Request::forGet(Uri::fromString('https://api.lbbw-test.prospeum.com/api/v01/supplier/search/'))
    );
    echo $response->getBody()->__toString();
} catch (HttpClientException $exception) {
    print_r($exception);
}


