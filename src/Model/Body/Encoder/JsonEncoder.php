<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Encoder;

use Artemeon\HttpClient\Model\Body\MediaType;

class JsonEncoder implements Encoder
{
    /** @var array|object */
    private $value;

    /**
     * JsonEncoder constructor.
     */
    private function __construct($value)
    {
        // json_encode needs UTF-8 encoded data
        if (!mb_check_encoding($value,'UTF-8')) {
           $value = utf8_encode($value);
        }

        $this->value = $value;
    }

    public static function fromArray(array $value): self
    {
        return new self($value);
    }

    public static function fromObject(object $value): self
    {
        return new self($value);
    }

    public function encode(): string
    {
        return json_encode($this->value);
    }

    public function getMimeType(): string
    {
       return MediaType::JSON;
    }
}
