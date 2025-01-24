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

namespace Artemeon\HttpClient\Http\Header;

/**
 * Interface for http header fields.
 */
interface HeaderField
{
    public const AUTHORIZATION = 'Authorization';
    public const REFERER = 'Referer';
    public const USER_AGENT = 'User-Agent';
    public const CONTENT_TYPE = 'Content-Type';
    public const CONTENT_LENGTH = 'Content-Length';
    public const HOST = 'Host';

    /**
     * Returns the name of the field.
     */
    public function getName(): string;

    /**
     * Returns the value of the field.
     */
    public function getValue(): string;
}
