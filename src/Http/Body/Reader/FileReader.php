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

namespace Artemeon\HttpClient\Http\Body\Reader;

use Artemeon\HttpClient\Exception\HttpClientException;
use SplFileObject;

/**
 * Reader to read body content from local and remote file system
 */
class FileReader implements Reader
{
    /** @var SplFileObject */
    private $fileObject;

    /**
     * FileReader constructor.
     *
     * @param SplFileObject $fileObject
     *
     * @throws HttpClientException
     */
    public function __construct(SplFileObject $fileObject)
    {
        if ($fileObject->isReadable()) {
            throw new HttpClientException('File is not readable: ' . $fileObject->getPathname());
        }

        $this->fileObject = $fileObject;
    }

    /**
     * Named construct to create an instance based on the given file and optional stream context options
     *
     * @param string $file Filename inclusive path
     * @param string|null $wrapper Optional: Activate stream wrapper option for the given wrapper (ftp, ssl, etc )
     * @param array|null $streamOptions Optional: Array of stream options for the given wrapper.
     *
     * @throws HttpClientException
     *
     * Example for ssl options: $wrapper = 'ssl', $streamOptions = ['verify_peer' => true]
     * @see https://www.php.net/manual/en/context.php
     */
    public static function fromFile(string $file, string $wrapper = null, array $streamOptions = null): self
    {
        if ($wrapper !== null && $streamOptions !== null) {
            $resource = stream_context_create();

            foreach ($streamOptions as $option => $value) {
                stream_context_set_option($resource, $wrapper, $option, $value);
            }
        } else {
            $resource = null;
        }

        return new self(new SplFileObject($file, 'r', false, $resource));
    }

    /**
     * @inheritDoc
     */
    public function read(): string
    {
        return $this->fileObject->fread($this->fileObject->getSize());
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension(): string
    {
        return $this->fileObject->getExtension();
    }
}
