<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

use Artemeon\HttpClient\Model\Body\Body;
use Artemeon\HttpClient\Model\Header\Headers;

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