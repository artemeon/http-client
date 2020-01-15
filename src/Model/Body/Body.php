<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body;

use function http_build_query;

/**
 * Value object zo cover all http body related content
 */
class Body
{
    /** @var int */
    private $length;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $value;

    /**
     * Body constructor.
     */
    private function __construct(string $type, string $value)
    {
        $this->mimeType = $type;
        $this->value = $value;
        $this->length = strlen($value);
    }

    /**
     * Named constructor to create an instance for json encoded content ("application/json")
     */
    public static function forJsonEncoded(string $jsonString): self
    {
        return new self(MediaType::JSON, $jsonString);
    }

    /**
     * Named constructor to create an instance for form url encoded content (application/x-www-form-urlencoded)
     */
    public static function forUrlEncodedFormData(array $formData)
    {
        return new self(MediaType::FORM_URL_ENCODED, http_build_query($formData));
    }

    /**
     * Named constructor to create an instance for multipart form data (multipart/form-data)
     */
    public static function forMultipartFormData(string $multipartData)
    {
        return new self(MediaType::MULTIPART_FORM_DATA, $multipartData);
    }

    /**
     * Returns the calculated content length
     */
    public function getContentLength(): int
    {
        return $this->length;
    }

    /**
     * Returns the associated MIME type string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Returns the content string
     */
    public function getContent(): string
    {
        return $this->value;
    }
}
