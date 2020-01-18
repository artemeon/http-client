<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Encoder;

use Artemeon\HttpClient\Model\Body\MediaType;

class MultipartFormDataEncoder implements Encoder
{
    /** @var string */
    private $boundary = "HTTP-Client";

    /** @var string[] */
    private $multiParts;

    /**
     * MultipartFormDataEncoder constructor.
     */
    private function __construct(string $boundary)
    {
        $this->boundary = $boundary;
        $this->multiParts = [];
    }

    public static function create(): self
    {
        return new self(uniqid('---------------------------'));
    }

    public function addFieldPart($fieldname, $value): self
    {
        return $this;
    }

    public function addFilePart(string $name, string $filename, string $contentType): self
    {
        return $this;
    }

    public function encode(): string
    {
        $encoded = '';

        foreach ($this->multiParts as $index => $part) {
            $encoded .= $this->boundary . "\r\n";
            $encoded .= $part;
        }

        $encoded .= $this->boundary . "--\r\n";

        return $encoded;
    }

    public function getMimeType(): string
    {
        return MediaType::MULTIPART_FORM_DATA;
    }
}
