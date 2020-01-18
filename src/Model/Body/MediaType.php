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

    /** @var string */
    public const PDF = "application/pdf";

    /** @var string */
    public const XML = "application/xml";

    /** @var string */
    public const BMP = "image/x-ms-bmp";

    /** @var string */
    public const GIF = "image/gif";

    /** @var string */
    public const UNKNOWN = "application/octet-stream";

    private static $extensionToType = [
        'json' => self::JSON,
        'pdf' => self::PDF,
        'xml' => self::XML,
        'bmp' => self::BMP,
        'gif' => self::GIF,
    ];

    public static function mapFileExtensionToMimeType(string $fileExtension): string
    {
        $fileExtension = strtolower($fileExtension);

        if (isset(self::$extensionToType[$fileExtension])) {
            return self::$extensionToType[$fileExtension];
        }

        return self::UNKNOWN;
    }
}
