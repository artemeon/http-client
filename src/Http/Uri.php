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
use Override;
use Psr\Http\Message\UriInterface;

/**
 * Class Url implements the PSR-7 UriInterface.
 */
class Uri implements UriInterface
{
    private string $query = '';
    private string $scheme = '';
    private string $host = '';
    private string $user = '';
    private string $password = '';
    private ?int $port = null;
    private string $path = '';
    private string $fragment = '';
    private const string UNRESERVED = 'a-zA-Z0-9_\-\.~';
    private const string DELIMITER = '!\$&\'\(\)\*\+,;=';

    private const array STANDARD_PORTS = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /**
     * @param string $uri Url string with protocol
     * @throws InvalidArgumentException
     */
    private function __construct(string $uri)
    {
        if ($uri === '') {
            return;
        }

        $this->query = $this->filterQueryOrFragment(parse_url($uri, PHP_URL_QUERY) ?? '');
        $this->scheme = $this->filterScheme(parse_url($uri, PHP_URL_SCHEME) ?? '');
        $this->host = $this->filterHost(parse_url($uri, PHP_URL_HOST) ?? '');
        $this->port = $this->filterPort(parse_url($uri, PHP_URL_PORT) ?? null);
        $this->fragment = $this->filterQueryOrFragment(parse_url($uri, PHP_URL_FRAGMENT) ?? '');
        $this->path = $this->filterPath(parse_url($uri, PHP_URL_PATH) ?? '');
        $this->user = parse_url($uri, PHP_URL_USER) ?? '';
        $this->password = parse_url($uri, PHP_URL_PASS) ?? '';
    }

    /**
     * Named constructor to create an instance based on the given url and query params.
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
    #[Override]
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPort(): ?int
    {
        if ($this->isStandardPort($this->scheme, $this->port)) {
            return null;
        }

        return $this->port;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUserInfo(): string
    {
        if ($this->user !== '') {
            if ($this->password !== '') {
                return $this->user . ':' . $this->password;
            }

            return $this->user;
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPath(): string
    {
        return preg_replace('#^/+#', '/', $this->path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __toString(): string
    {
        $uri = ($this->getScheme() !== '') ? $this->getScheme() . ':' : '';

        if ($this->getAuthority() !== '') {
            $uri .= '//' . $this->getAuthority();
        }

        // not normalized like //valid/path
        if ($this->path !== '') {
            $uri .= $this->path;
        }

        if ($this->getQuery() !== '') {
            $uri .= '?' . $this->getQuery();
        }

        if ($this->getFragment() !== '') {
            $uri .= '#' . $this->getFragment();
        }

        return $uri;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAuthority(): string
    {
        $authority = ($this->getPort() === null) ? $this->host : $this->host . ':' . $this->port;

        if ($this->getUserInfo() !== '') {
            return $this->getUserInfo() . '@' . $authority;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withScheme(string $scheme): self
    {
        $this->filterScheme($scheme);

        $cloned = clone $this;
        $cloned->scheme = strtolower($scheme);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withUserInfo(string $user, ?string $password = null): self
    {
        $user = $this->filterUserInfoComponent($user);
        if ($password !== null) {
            $password = $this->filterUserInfoComponent($password);
        }

        $cloned = clone $this;

        // Empty string for the user is equivalent to removing user
        if ($user === '') {
            $cloned->user = '';
            $cloned->password = '';
        } else {
            $cloned->user = $user;
            $cloned->password = $password ?? '';
        }

        return $cloned;
    }

    private function filterUserInfoComponent(string $component): string
    {
        return preg_replace_callback(
            '/(?:[^%' . self::UNRESERVED . self::DELIMITER . ']+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $component,
        );
    }

    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode((string) $match[0]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withHost(string $host): self
    {
        $cloned = clone $this;
        $cloned->host = $cloned->filterHost($host);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withPort(?int $port): self
    {
        $cloned = clone $this;
        $cloned->port = $cloned->filterPort($port);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withPath(array | bool | int | string $path): self
    {
        $cloned = clone $this;
        $cloned->path = $cloned->filterPath($path);

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withQuery(string $query): self
    {
        $cloned = clone $this;
        $cloned->query = $cloned->filterQueryOrFragment($query);

        return $cloned;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    #[Override]
    public function withFragment(string $fragment): self
    {
        $cloned = clone $this;
        $cloned->fragment = $cloned->filterQueryOrFragment($fragment);

        return $cloned;
    }

    /**
     * Filter and validate the port.
     *
     * @throws InvalidArgumentException
     */
    private function filterPort(?int $port): ?int
    {
        if ($port !== null) {
            if ($port < 0 || $port > 65535) {
                throw new InvalidArgumentException("port: $port must be in a range between 0 and 65535");
            }
        }

        return $port;
    }

    /**
     * Filter and validate the scheme.
     *
     * @throws InvalidArgumentException
     */
    private function filterScheme(string $scheme): string
    {
        return strtolower(trim($scheme));
    }

    /**
     * Filter and validate the host.
     *
     * @throws InvalidArgumentException
     */
    private function filterHost(string $host): string
    {
        return strtolower(trim($host));
    }

    /**
     * Filter, validate and encode the path.
     *
     * @throws InvalidArgumentException
     */
    private function filterPath(array | bool | int | string $path): string
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('path must be a string');
        }

        $pattern = '/(?:[^' . self::UNRESERVED . self::DELIMITER . "%:@\/]++|%(?![A-Fa-f0-9]{2}))/";

        return preg_replace_callback($pattern, [$this, 'encode'], $path);
    }

    /**
     * * Filter, validate and encode the query or fragment.
     *
     * @throws InvalidArgumentException
     */
    private function filterQueryOrFragment(string $fragment): string
    {
        $pattern = '/(?:[^' . self::UNRESERVED . self::DELIMITER . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/';

        return preg_replace_callback($pattern, [$this, 'encode'], $fragment);
    }

    /**
     * Checks if the given scheme uses their standard port.
     */
    private function isStandardPort(string $scheme, ?int $port): bool
    {
        if (!isset(self::STANDARD_PORTS[$scheme])) {
            return false;
        }

        return self::STANDARD_PORTS[$scheme] === $port;
    }

    /**
     * Encoding for path, query and fragment characters.
     *
     * @param string[] $matches
     */
    private function encode(array $matches): string
    {
        return rawurlencode($matches[0]);
    }
}
