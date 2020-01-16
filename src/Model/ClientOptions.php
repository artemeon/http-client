<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

class ClientOptions
{
    /** @var bool */
    private $maxAllowRedirects;

    /** @var int */
    private $timeout;

    /** @var bool */
    private $verifySsl;

    /** @var string string */
    private $customCaBundlePath;

    /** @var int */
    private $maxRedirects;

    /** @var bool */
    private $addReferer;

    public static function fromDefaults(): self
    {
        $instance = new self();
        $instance->maxAllowRedirects = true;
        $instance->timeout = 10;
        $instance->verifySsl = true;
        $instance->customCaBundlePath = '';
        $instance->maxRedirects = 5;
        $instance->addReferer = true;

        return $instance;
    }

    public function setMaxAllowedRedirects(int $max = 5): void
    {
        $this->maxAllowRedirects = $max;
    }

    /**
     * Disable SSL certificate verification
     */
    public function disableCertificateVerification(): void
    {
        $this->verifySsl = false;
    }

    /**
     * @return bool
     */
    public function isCertificateVerificationEnabled(): bool
    {
        return $this->verifySsl;
    }

    /**
     * @return string
     */
    public function getCustomCaBundlePath(): string
    {
        return $this->customCaBundlePath;
    }

    /**
     * Set the timeout in seconds for requests
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * As default we use the CA bundle provided by operating system. Use this function to allows custom
     * CA bundle certificates.
     */
    public function setCustomCaBundlePath(string $customCaBundlePath): void
    {
        $this->customCaBundlePath = $customCaBundlePath;
    }

    /**
     * @param int $maxRedirects
     */
    public function setMaxRedirects(int $maxRedirects): void
    {
        $this->maxRedirects = $maxRedirects;
    }

    /**
     * @param bool $allowRedirects
     */
    public function disableRedirects(): void
    {
        $this->maxAllowRedirects = false;
    }

    /**
     * @param bool $addReferer
     */
    public function disableReferer(): void
    {
        $this->addReferer = false;
    }

    /**
     * @return int
     */
    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * @return bool
     */
    public function isRedirectAllowed(): bool
    {
        return $this->maxAllowRedirects;
    }

    /**
     * @return bool
     */
    public function isRefererAllowed(): bool
    {
        return $this->addReferer;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return bool
     */
    public function hasCustomCaBundlePath(): bool
    {
        return !empty($this->getCustomCaBundlePath());
    }



}