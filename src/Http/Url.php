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
use Artemeon\HttpClient\Psr7\UriInterfaceSubset;

use function count;
use function http_build_query;
use function parse_url;

use const PHP_URL_FRAGMENT;
use const PHP_URL_HOST;
use const PHP_URL_PASS;
use const PHP_URL_PORT;
use const PHP_URL_SCHEME;
use const PHP_URL_USER;

class Url implements UriInterfaceSubset
{
    /** @var string */
    private $url;

    /** @var string */
    private $query = '';

    /** @var string */
    private $scheme;

    /** @var string */
    private $host;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var int|null */
    private $port;

    /** @var string */
    private $path;

    /** @var string */
    private $fragment;

    /**
     * Url constructor.
     *
     * @param string $url Url string with protocol
     * @param array $queryParams Query params array: ["varName" => value]
     *
     * @throws HttpClientException
     */
    private function __construct(string $url, array $queryParams)
    {
        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new HttpClientException('Url is invalid: ' . $url);
        }

        $this->url = $url;
        $this->scheme = parse_url($url, PHP_URL_SCHEME) ?? '';
        $this->host = parse_url($url, PHP_URL_HOST) ?? '';
        $this->port = parse_url($url, PHP_URL_PORT);
        $this->fragment = parse_url($url, PHP_URL_FRAGMENT) ?? '';
        $this->user = parse_url($url, PHP_URL_USER) ?? '';
        $this->password = parse_url($url, PHP_URL_PASS) ?? '';
    }

    /**
     * Named constructor to create an instance based on the given url and query params
     *
     * @param string $url Url string with protocol
     * @param array $queryParams Query params array: ["varName" => value]
     *
     * @throws HttpClientException
     */
    public static function withQueryParams(string $url, array $queryParams): self
    {
        return new self($url, $queryParams);
    }

    /**
     * Named constructor to create an instance based on the given url string.
     *
     * @param string $url Url string with protocol
     *
     * @throws HttpClientException
     */
    public static function fromString(string $url): self
    {
        return new self($url, []);
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        if (!empty($this->user)) {
            if (!empty($this->password)) {
                return $this->user . ':' . $this->password;
            }
            return $this->user;
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->url;
    }
}
