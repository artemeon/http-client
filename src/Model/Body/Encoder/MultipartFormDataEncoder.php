<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Encoder;

use Artemeon\HttpClient\Model\Body\MediaType;

class MultipartFormDataEncoder implements Encoder
{
    /** @var string */
    private $boundary = "HTTP-Client";

    /** @var string[] */
    private $multiParts = [];

    public function encode(): string
    {
        $encoded = '';

        foreach ($this->multiParts as $index => $part) {
            $encoded .= '---------------------------' . $this->boundary . "\r\n";
            $encoded .= $part;
        }

        $encoded .= '---------------------------' . $this->boundary . "--\r\n";

        return $encoded;
    }

    public function getMimeType(): string
    {
        return MediaType::MULTIPART_FORM_DATA;
    }
}
