<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\System;

use Artemeon\HttpClient\Model\Authorisation;
use Artemeon\HttpClient\Model\Body\Content;
use Artemeon\HttpClient\Model\Header\Header;
use Artemeon\HttpClient\Model\Header\HeaderBag;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Service\GuzzleHttpClient;

use function json_encode;

$headerBag = new HeaderBag();
$headerBag->addHeader(Header::forAuthorisation(Authorisation::forAuthBasic('John.Doe', 'geheim')));
$headerBag->addHeader(Header::forUserAgent());

$content = Content::forJsonEncoded(json_encode(["test" => 2342]));

$httpClient = new GuzzleHttpClient();
$httpClient->send(Request::forPost('http://test.de', $content, $headerBag));