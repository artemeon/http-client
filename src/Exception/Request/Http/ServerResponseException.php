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

namespace Artemeon\HttpClient\Exception\Request\Http;

/**
 * Exception class to catch all server related http errors (500 range)
 */
class ServerResponseException extends ResponseException
{
    /** @var string */
    protected $supportedStatusCodes = "500:511";
}