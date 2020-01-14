<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

class HeaderFields
{
    /** @var string */
    const AUTHORISATION = 'Authorization';

    /** @var string */
    const REFERER = "Referer";

    /** @var string  */
    const USER_AGENT = "User-Agent";

    /** @var string  */
    const CONTENT_TYPE = 'Content-Type';

    /** @var string  */
    const CONTENT_LENGTH = 'Content-Length';
}