<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Body\Encoder;

use Artemeon\HttpClient\Model\Body\MediaType;

class FormUrlEncoder implements Encoder
{
    /** @var array */
    private $formValues;

    /**
     * FormUrlEncoder constructor.
     */
    public function __construct(array $formValues)
    {
        $this->formValues = $formValues;
    }

    public static function fromArray(array $formValues): self
    {
        return new self($formValues);
    }

    public function encode(): string
    {
        return http_build_query($this->formValues);
    }

    public function getMimeType(): string
    {
        return MediaType::FORM_URL_ENCODED;
    }
}
