<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

/**
 * Class to create constants for common used header fields
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

    public function getName(): string ;

    public function getValue(): string;
}
