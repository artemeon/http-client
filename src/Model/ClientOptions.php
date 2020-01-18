<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model;

class ClientOptions
{
    /** @var bool */
    private $allowRedirects;

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
        $instance->allowRedirects = true;
        $instance->timeout = 10;
        $instance->verifySsl = true;
        $instance->customCaBundlePath = '';
        $instance->maxRedirects = 5;
        $instance->addReferer = true;

        return $instance;
    }

    public function optSetMaxAllowedRedirects(int $max): void
    {
        $this->maxRedirects = $max;
    }

    public function optDisableRedirects(): void
    {
        $this->allowRedirects = false;
    }

    /**
     * @return bool
     */
    public function isRedirectAllowed(): bool
    {
        return $this->allowRedirects;
    }

    /**
     * Disable SSL certificate verification
     */
    public function optDisableCertificateVerification(): void
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
     * As default we use the CA bundle provided by operating system. Use this function to allows custom
     * CA bundle certificates.
     */
    public function confSetCustomCaBundlePath(string $customCaBundlePath): void
    {
        $this->customCaBundlePath = $customCaBundlePath;
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
    public function optSetTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $maxRedirects
     */
    public function optSetMaxRedirects(int $maxRedirects): void
    {
        $this->maxRedirects = $maxRedirects;
    }

    /**
     * @return int
     */
    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * @param bool $addReferer
     */
    public function optDisableReferer(): void
    {
        $this->addReferer = false;
    }

    /**
     * @return bool
     */
    public function isRefererAllowed(): bool
    {
        return $this->addReferer;
    }

    /**
     * @return bool
     */
    public function hasCustomCaBundlePath(): bool
    {
        return !empty($this->getCustomCaBundlePath());
    }



}
