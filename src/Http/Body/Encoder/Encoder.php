<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Body\Encoder;

/**
 * Interface for http body Encoder
 */
interface Encoder
{
    /**
     * Encodes the body content
     */
    public function encode(): string;

    /**
     * Returns the supported MimeType
     */
    public function getMimeType(): string;
}
