<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Encoder;

interface Encoder
{
    public function encode(): string;

    public function getMimeType(): string;
}
