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
     * @param string $uri Url string with protocol
     * @throws InvalidArgumentException
     */
    private function __construct(string $uri)
    {
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
        $uri = (!empty($this->getScheme())) ? $this->getScheme() . ':' : '';

        if (!empty($this->getAuthority())) {
            $uri .= '//' . $this->getAuthority();
        }

        if (!empty($this->getPath())) {
            $uri .= $this->getPath();
        }

        if (!empty($this->getQuery())) {
            $uri .= '?' . $this->getQuery();
        }

        if (!empty($this->getFragment())) {
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

        if (!empty($this->getUserInfo())) {
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

        // Empty string for the user is equivalent to removing user
        if (empty($user)) {
            $cloned->user = '';
            $cloned->password = '';
        } else {
            $cloned->user = strval($user);

            if ($password !== null) {
                $cloned->password = strval($password);
            }
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
        if (is_string($path)) {
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
        if (is_string($query)) {
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
     */
    private function assertIsValid(): void
    {
        $uri = $this->__toString();

        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Url is invalid: ' . $uri);
        }
    }
}
