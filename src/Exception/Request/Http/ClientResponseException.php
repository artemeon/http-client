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
 * Exception class to catch all client related http errors (400 range)
 */
class ClientResponseException extends ResponseException
{
    /** @var string */
    protected $supportedStatusCodes = "400:450";
}