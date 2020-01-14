<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

class HeaderBag
{
    /** @var Header[] */
    private $headers;

    public function addHeader(Header $header): void {
        $this->headers[] = $header;
    }
}
