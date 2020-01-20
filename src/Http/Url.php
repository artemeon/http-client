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
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

use function count;
use function http_build_query;
use function is_int;
use function is_string;
use function parse_url;
use function strval;

use const PHP_URL_HOST;
use const PHP_URL_PASS;
use const PHP_URL_PORT;
use const PHP_URL_SCHEME;
use const PHP_URL_USER;

class Url implements UriInterface
{
    /** @var string */
    private $url;

    /** @var string */
    private $queryString = '';

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
    private $authority;

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
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new HttpClientException('Url is invalid: ' . $url);
        }

        $this->url = $url;
        $this->scheme = parse_url($url, PHP_URL_SCHEME) ?? '';
        $this->host = parse_url($url, PHP_URL_HOST) ?? '';
        $this->user = parse_url($url, PHP_URL_USER) ?? '';
        $this->port = parse_url($url, PHP_URL_PORT);
        $this->password = parse_url($url, PHP_URL_PASS) ?? '';
        $this->authority = $this->user . $this->host . $this->port;

        if (count($queryParams) > 0) {
            $this->queryString = http_build_query($queryParams);
        }
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
    public function withScheme($scheme)
    {
        $this->assertString($scheme);

        $cloned = clone $this;
        $cloned->scheme = $scheme;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $this->assertString($user);

        if ($password !== null) {
            $this->assertString($password);
        }

        $cloned = clone $this;

        // Remove all user info if user is empty
        if (empty($user)) {
            $cloned->user = '';
            $cloned->password = null;

            return $cloned;
        }

        $cloned->user = $user;
        $cloned->password = $password;

        return $cloned;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $host = strval($host);
        $cloned = clone $this;

        if (empty($host)) {
            return $cloned->host = '';
        }

        return $cloned->host = $host;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        if ($port !== null && !is_int($port)) {
            throw new \InvalidArgumentException('Port int or null');
        }

        if ($port < 0 || $port > 65535) {
            throw new \InvalidArgumentException('Port Must be between 0 and 65535');
        }

        $cloned = clone $this;
        $cloned->port = $port;

        return $cloned->port;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        // TODO: Implement withPath() method.
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        // TODO: Implement withQuery() method.
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        // TODO: Implement withFragment() method.
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
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
    public function getUser(): string
    {
        return $this->user;
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
    public function getAuthority(): string
    {
        return $this->authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        // TODO: Implement getUserInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        // TODO: Implement getPath() method.
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        // TODO: Implement getQuery() method.
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        // TODO: Implement getFragment() method.
    }

    /**
     * Convert to string
     *
     * @see https://www.php.net/manual/de/language.oop5.magic.php#object.tostring
     */
    public function __toString(): string
    {
        if (!empty($this->queryString)) {
            return $this->url . '?' . $this->queryString;
        }

        return $this->url;
    }

    /**
     * @param $string
     *
     * @throws InvalidArgumentException
     */
    private function assertString($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException('Parameter mus be a string');
        }
    }
}
