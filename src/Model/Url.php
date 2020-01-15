<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

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
     * @param string $url
     * @param array $queryParams
     */
    private function __construct(string $url, array $queryParams)
    {
        $this->url = $url;

        if (count($queryParams) > 0) {
            $this->queryString = http_build_query($queryParams);
        }
    }

    /**
     * @param string $url
     * @return static
     */
    public static function fromString(string $url): self
    {
        return new self($url, []);
    }

    /**
     * @param string $url
     * @param array $queryParams
     * @return static
     */
    public static function withQueryParams(string $url, array $queryParams): self
    {
        return new self($url, $queryParams);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!empty($this->queryString)) {
            return $this->url . '?' . $this->queryString;
        }

        return  $this->url;
    }
}