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

namespace Artemeon\HttpClient\Http\Body\Encoder;

use Artemeon\HttpClient\Exception\RuntimeException;
use Psr\Http\Message\StreamInterface;

/**
 * Interface for http body Encoder.
 */
interface Encoder
{
    /**
     * Encodes the body content.
     *
     * @throws RuntimeException
     */
    public function encode(): StreamInterface;

    /**
     * Returns the supported MimeType.
     */
    public function getMimeType(): string;
}
