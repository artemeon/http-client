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

use Artemeon\HttpClient\Exception\InvalidArgumentException;
use Artemeon\HttpClient\Http\Header\Headers;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Response class
 */
class Response extends Message implements ResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase;

    /**
     * @param int $statusCode The http status code
     * @param string $version The http version number without http prefix
     * @param StreamInterface $body The body content stream
     * @param Headers $headers The Headers collection class
     * @param string $reasonPhrase The http response reason phrase
     */
    public function __construct(
        int $statusCode,
        string $version,
        StreamInterface $body = null,
        Headers $headers = null,
        string $reasonPhrase = ''
    ) {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        parent::__construct($headers, $body, $version);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if (!is_string($reasonPhrase)) {
            throw new InvalidArgumentException('reasonPhrase must be a string value');
        }

        if (!is_int($code)) {
            throw  new InvalidArgumentException('code must be a integer value');
        }

        if ($code < 100 || $code >= 600) {
            throw new \InvalidArgumentException('code must be an integer value between 100 and 599');
        }

        $cloned = clone $this;
        $cloned->statusCode = $code;
        $cloned->reasonPhrase = $reasonPhrase;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
