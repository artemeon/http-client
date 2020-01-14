<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

use Artemeon\HttpClient\Model\Header\HeaderBag;

class Response
{
    /** @var int */
    private $statusCode;

    /** @var string */
    private $version;

    /** @var string */
    private $body;

    /** @var HeaderBag */
    private $headerBag;
}