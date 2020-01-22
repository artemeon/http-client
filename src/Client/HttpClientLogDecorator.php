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

namespace Artemeon\HttpClient\Client;

use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;

class HttpClientLogDecorator implements HttpClient
{

    public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        //void
    }
}