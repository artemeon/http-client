<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http;

use Artemeon\HttpClient\Exception\HttpClientException;

use function count;
use function http_build_query;

class Url
{
    /** @var string */
    private $url;

    /** @var string */
    private $queryString = '';

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

        if (count($queryParams) > 0) {
            $this->queryString = http_build_query($queryParams);
        }
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
}
