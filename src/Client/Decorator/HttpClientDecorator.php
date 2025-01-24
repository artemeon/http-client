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

namespace Artemeon\HttpClient\Client\Decorator;

use Artemeon\HttpClient\Client\HttpClient;
use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

/**
 * Abstract base class for the decorator pattern.
 */
abstract class HttpClientDecorator implements HttpClient
{
    /**
     * HttpClientDecorator constructor.
     */
    public function __construct(protected HttpClient $httpClient)
    {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    abstract public function send(Request $request, ?ClientOptions $clientOptions = null): Response;
}
