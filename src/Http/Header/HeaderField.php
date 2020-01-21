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
 * Interface for http header fields
 */
interface HeaderField
{
    /** @var string */
    public const AUTHORISATION = 'Authorization';

    /** @var string */
    public const REFERER = "Referer";

    /** @var string */
    public const USER_AGENT = "User-Agent";

    /** @var string */
    public const CONTENT_TYPE = 'Content-Type';

    /** @var string */
    public const CONTENT_LENGTH = 'Content-Length';

    /**
     * Returns the name of the field
     */
    public function getName(): string;

    /**
     * Returns the value of the field
     */
    public function getValue(): string ;
}
