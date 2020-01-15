<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

/**
 * Header collection class for http requests and responses
 */
class Headers
{
    /** @var Header[] */
    private $headers;

    /**
     * Adds a header to the collection
     */
    public function addHeader(Header $header): void
    {
        $this->headers[] = $header;
    }
}
