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

use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Header\Headers;

class Response
{
    /** @var int */
    private $statusCode;

    /** @var string */
    private $version;

    /** @var string */
    private $body;

    /** @var Headers */
    private $headerBag;

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param string $version
     * @param Body $body
     * @param Headers $headerBag
     */
    public function __construct(int $statusCode, string $version, string $body, Headers $headerBag)
    {
        $this->statusCode = $statusCode;
        $this->version = $version;
        $this->body = $body;
        $this->headerBag = $headerBag;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return Body
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        return $this->headerBag;
    }
}
