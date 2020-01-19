<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Body\Encoder;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\MediaType;

/**
 * Encoder for "multipart/form-data" encoded body content
 */
class MultipartFormDataEncoder implements Encoder
{
    /** @var string */
    private $boundary;

    /** @var string[] */
    private $multiParts;

    /** @var string */
    private $crlf = "\r\n";

    /**
     * MultipartFormDataEncoder constructor.
     *
     * @param string $boundary Boundary string 7bit US-ASCII
     */
    private function __construct(string $boundary)
    {
        $this->boundary = $boundary;
        $this->multiParts = [];
    }

    /**
     * Named constructor to create an instance
     */
    public static function create(): self
    {
        $boundary = uniqid('---------------------------');
        return new self($boundary);
    }

    /**
     * Add a new multipart section for form fields
     *
     * @param string $fieldName Name of the form field
     * @param string $value Value of the form field
     *
     * @throws HttpClientException
     */
    public function addFieldPart(string $fieldName, string $value): self
    {
        $encoding = $this->detectEncoding($value);

        $part = $this->boundary . $this->crlf;
        $part .= sprintf('Content-Disposition: form-data; name="%s"', $fieldName) . $this->crlf;
        $part .= sprintf('Content-Type: text/plain; charset=%s', $encoding) . $this->crlf;
        $part .= $this->crlf;
        $part .= $value . $this->crlf;

        $this->multiParts[] = $part;

        return $this;
    }

    /**
     * Add a new multipart section for file upload fields
     *
     * @param string $name Name of the form field
     * @param string $fileName Name of the file, with a valid file extension
     * @param string $fileContent Binary string of the file
     */
    public function addFilePart(string $name, string $fileName, string $fileContent): self
    {
        $fileExtension = preg_replace('/^.*\.([^.]+)$/', '$1', $fileName);

        $part = $this->boundary . $this->crlf;
        $part .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"', $name, $fileContent) . $this->crlf;
        $part .= sprintf('Content-Type: %s', MediaType::mapFileExtensionToMimeType($fileExtension)) . $this->crlf;
        $part .= $this->crlf;
        $part .= $fileContent . $this->crlf;

        $this->multiParts[] = $part;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function encode(): string
    {
        $parts = '';

        foreach ($this->multiParts as $parts) {
            $parts .= $parts;
        }

        return $parts . $this->boundary . "--\r\n";
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): string
    {
        return MediaType::MULTIPART_FORM_DATA . '; ' . $this->boundary;
    }

    /**
     * Detects the encoding of the given string
     *
     * @throws HttpClientException
     */
    private function detectEncoding(string $value): string
    {
        $encoding = mb_detect_encoding($value);

        if ($encoding === false) {
            throw new HttpClientException("Cant't detect encoding for multipart");
        }

        return $encoding;
    }
}
