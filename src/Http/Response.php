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

namespace Artemeon\HttpClient\Http;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Header\Headers;

class Response extends Message
{
    /** @var int */
    private $statusCode;

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param string $version
     * @param Body $body
     * @param Headers $headers
     *
     * @throws HttpClientException
     */
    public function __construct(int $statusCode, float $version, string $body, Headers $headers)
    {
        $this->statusCode = $statusCode;
        parent::__construct($headers, $body, $version);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
