<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body;

/**
 * Class to create constants for common media types
 */
class MediaType
{
    /** @var string */
    public const JSON = "application/json";

    /** @var string */
    public const FORM_URL_ENCODED = "application/x-www-form-urlencoded";

    /** @var string */
    public const MULTIPART_FORM_DATA = "multipart/form-data";

}