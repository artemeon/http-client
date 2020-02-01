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
use Artemeon\HttpClient\Client\HttpClientFactory;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use GuzzleHttp\MessageFormatter;

require '../../vendor/autoload.php';

$transactions = [];
$formatter = new MessageFormatter(MessageFormatter::DEBUG);

try {
    $apiClient = ClientCredentialsDecorator::fromClientCredentials(
        Uri::fromString('https://api.lbbw-test.prospeum.com/o/token/'),
        ClientCredentials::fromClientId(
            'yoour_client_id',
            'your_client_secret',
            'read_suppliers'
        ),
        HttpClientFactory::withTransactionMiddleware($transactions)
    );

    $apiClient->send(Request::forGet(Uri::fromString('https://api.lbbw-test.prospeum.com/api/v01/supplier/search/')));

    foreach ($transactions as $transaction) {
        echo nl2br($formatter->format($transaction['request'], $transaction['response']));
    }
} catch (HttpClientException $exception) {
    foreach ($transactions as $transaction) {
        echo nl2br($formatter->format($transaction['request'], $transaction['response'], $exception));
    }
}
