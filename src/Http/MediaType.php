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
 * Static class to describe media type MIME types.
 */
class MediaType
{
    public const string JSON = 'application/json';
    public const string JSON_API = 'application/vnd.api+json';
    public const string FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    public const string MULTIPART_FORM_DATA = 'multipart/form-data';
    public const string PDF = 'application/pdf';
    public const string XML = 'application/xml';
    public const string BMP = 'image/x-ms-bmp';
    public const string GIF = 'image/gif';
    public const string JPG = 'image/jpeg';
    public const string PNG = 'image/png';
    public const string UNKNOWN = 'application/octet-stream';

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
     * Static helper function to map a file extension to the related MIME type.
     *
     * @param string $fileExtension The file extension string
     */
    public static function mapFileExtensionToMimeType(string $fileExtension): string
    {
        $fileExtension = strtolower($fileExtension);

        return self::$extensionToType[$fileExtension] ?? self::UNKNOWN;
    }
}
