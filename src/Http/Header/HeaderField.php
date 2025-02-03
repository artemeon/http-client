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
    public const string AUTHORIZATION = 'Authorization';
    public const string REFERER = 'Referer';
    public const string USER_AGENT = 'User-Agent';
    public const string CONTENT_TYPE = 'Content-Type';
    public const string CONTENT_LENGTH = 'Content-Length';
    public const string HOST = 'Host';

    /**
     * Returns the name of the field.
     */
    public function getName(): string;

    /**
     * Returns the value of the field.
     */
    public function getValue(): string;
}
