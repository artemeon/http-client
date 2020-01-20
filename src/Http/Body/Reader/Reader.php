<?php

/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
