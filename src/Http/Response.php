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
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Psr7\ResponseInterfaceSubset;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterfaceSubset
{
    /** @var int */
    private $statusCode;

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param string $version
     * @param StreamInterface $body
     * @param Headers $headers
     *
     * @throws HttpClientException
     */
    public function __construct(int $statusCode, string $version, StreamInterface $body, Headers $headers)
    {
        $this->statusCode = $statusCode;
        parent::__construct($headers, $body, $version);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
