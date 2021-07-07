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

namespace Artemeon\HttpClient\Http;

/**
 * Static class to describe media type MIME types
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

    /** @var string  */
    public const JPG = "image/jpeg";

    /** @var string  */
    public const PNG = "image/png";

    /** @var string */
    public const UNKNOWN = "application/octet-stream";

    /** @var string[] */
    private static array $extensionToType = [
        'json' => self::JSON,
        'pdf' => self::PDF,
        'xml' => self::XML,
        'bmp' => self::BMP,
        'gif' => self::GIF,
        'jpg' => self::JPG,
        'png' => self::PNG,
    ];

    /**
     * Static helper function to map a file extension to the related MIME type
     *
     * @param string $fileExtension The file extension string
     */
    public static function mapFileExtensionToMimeType(string $fileExtension): string
    {
        $fileExtension = strtolower($fileExtension);

        if (isset(self::$extensionToType[$fileExtension])) {
            return self::$extensionToType[$fileExtension];
        }

        return self::UNKNOWN;
    }
}
