<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Body\Reader;

/**
 * Reader interface for body content
 */
interface Reader
{
    /**
     * Reads the body content
     */
    public function read(): string;

    /**
     * Returns the file extension of the read file
     */
    public function getFileExtension(): string;
}
