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
use Psr\Http\Message\UriInterface;

/**
 * Class Url implements the PSR-7 UriInterface
 */
class Uri implements UriInterface
{
    /** @var string */
    private $query = '';

    /** @var string */
    private $scheme = '';

    /** @var string */
    private $host = '';

    /** @var string */
    private $user = '';

    /** @var string */
    private $password = '';

    /** @var int|null */
    private $port;

    /** @var string */
    private $path = '';

    /** @var string */
    private $fragment = '';

    /**
     * Url constructor.
     *
     * @param string $uri Url string with protocol
     * @throws InvalidArgumentException
     */
    private function __construct(string $uri)
    {
        if (!empty(trim($uri))) {
            $this->query = parse_url($uri, PHP_URL_QUERY) ?? '';
            $this->scheme = strtolower(parse_url($uri, PHP_URL_SCHEME) ?? '');
            $this->host = parse_url($uri, PHP_URL_HOST) ?? '';
            $this->port = parse_url($uri, PHP_URL_PORT);
            $this->fragment = parse_url($uri, PHP_URL_FRAGMENT) ?? '';
            $this->user = parse_url($uri, PHP_URL_USER) ?? '';
            $this->password = parse_url($uri, PHP_URL_PASS) ?? '';
            $this->path = parse_url($uri, PHP_URL_PATH) ?? '';

            $this->assertIsValid();
        }
    }

    /**
     * Named constructor to create an instance based on the given url and query params
     *
     * @param string $uri Url string with protocol
     * @param array $queryParams Query params array: ["varName" => value]
     * @throws InvalidArgumentException
     */
    public static function fromQueryParams(string $uri, array $queryParams): self
    {
        if (count($queryParams) > 0) {
            $uri .= '?' . http_build_query($queryParams);
        }

        return new self($uri);
    }

    /**
     * Named constructor to create an instance based on the given url string.
     *
     * @param string $uri Url string with protocol
     * @throws InvalidArgumentException
     */
    public static function fromString(string $uri): self
    {
        return new self($uri);
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
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(): string
    {
        if (strlen($this->user) > 0) {
            if (strlen($this->password) > 0) {
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
        $uri = (strlen($this->getScheme()) > 0) ? $this->getScheme() . ':' : '';

        if (strlen($this->getAuthority()) > 0) {
            $uri .= '//' . $this->getAuthority();
        }

        if (strlen($this->getPath()) > 0) {
            $uri .= $this->getPath();
        }

        if (strlen($this->getQuery()) > 0) {
            $uri .= '?' . $this->getQuery();
        }

        if (strlen($this->getFragment()) > 0) {
            $uri .= '#' . $this->getFragment();
        }

        return $uri;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        $authority = ($this->port === null) ? $this->host : $this->host . ':' . $this->port;

        if (strlen($this->getUserInfo()) > 0) {
            return $this->getUserInfo() . '@' . $authority;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme): self
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('scheme must be a lowercase string');
        }

        $cloned = clone $this;
        $cloned->scheme = strtolower($scheme);
        $cloned->assertIsValid();

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null): self
    {
        $cloned = clone $this;
        $user = trim(strval($user));
        $password = trim(strval($password));

        // Empty string for the user is equivalent to removing user
        if (strlen($user) === 0) {
            $cloned->user = '';
            $cloned->password = '';
        } else {
            $cloned->user = $user;
            $cloned->password = $password ?? '';
        }

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host): self
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('host must be a string value');
        }

        $cloned = clone $this;
        $cloned->host = strtolower($host);
        $cloned->assertIsValid();

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port): self
    {
        if ($port !== null) {
            if (!is_int($port)) {
                throw new InvalidArgumentException('port must be a integer value');
            }

            if ($port < 0 || $port > 65535) {
                throw new InvalidArgumentException("port: $port must be in a range between 0 and 65535");
            }
        }

        $cloned = clone $this;
        $cloned->port = $port;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path): self
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('path must be a string value');
        }

        $cloned = clone $this;
        $cloned->path = $path;
        $cloned->assertIsValid();

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query): self
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException('query must be a string value');
        }

        $cloned = clone $this;
        $cloned->query = $query;
        $cloned->assertIsValid();

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment): self
    {
        $cloned = clone $this;
        $cloned->fragment = strval($fragment);

        return $cloned;
    }

    /**
     * @throws InvalidArgumentException
     * @see https://mathiasbynens.be/demo/url-regex gruber v2
     */
    private function assertIsValid(): void
    {
        $uri = $this->__toString();
        $pattern = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))#iS";

        if ($uri === '/') {
            return;
        }

        if (preg_match($pattern, $uri) !== 1) {
            throw new InvalidArgumentException('Uri is invalid: ' . $uri);
        }
    }
}
