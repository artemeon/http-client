<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

use Artemeon\HttpClient\Exception\HttpClientException;

/**
 * Header collection class for http requests and responses
 */
class Headers
{
    /** @var Header[] */
    private $headers;

    /**
     * Adds a header to the collection
     * @throws HttpClientException
     */
    public function addHeader(Header $header): void
    {
        $fieldName = $header->getFieldName();

        if (isset($this->headers[$fieldName])) {
            throw HttpClientException::forAlreadyRegisteredHeaderFields($fieldName);
        }

        $this->headers[$fieldName] = $header;
    }

    public function hasHeader($headerField)
    {

    }
}
