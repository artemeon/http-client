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

use Artemeon\HttpClient\Http\Header\Headers;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Response class.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @param int $statusCode The http status code
     * @param string $version The http version number without http prefix
     * @param StreamInterface $body The body content stream
     * @param Headers $headers The Headers collection class
     * @param string $reasonPhrase The http response reason phrase
     */
    public function __construct(
        private int $statusCode,
        string $version,
        ?StreamInterface $body = null,
        ?Headers $headers = null,
        private string $reasonPhrase = '',
    ) {
        parent::__construct($headers, $body, $version);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
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
    #[Override]
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
