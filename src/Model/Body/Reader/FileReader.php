<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Reader;

use SplFileObject;

class FileReader implements Reader
{
    /** @var SplFileObject */
    private $fileObject;

    public function read(): string
    {
        return $this->fileObject->fread($this->fileObject->getSize());
    }

    public function getFileExtension(): string
    {
        return $this->fileObject->getExtension();
    }

}
