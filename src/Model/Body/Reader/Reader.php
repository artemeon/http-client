<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Reader;

interface Reader
{
    public function read(): string;

    public function getFileExtension(): string;
}
