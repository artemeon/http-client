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

namespace Artemeon\HttpClient\Client\Options;

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

    /**
     * Option to disable redirects.
     */
    public function optDisableRedirects(): void
    {
        $this->allowRedirects = false;
    }

    /**
     * Is redirect allowed
     */
    public function isRedirectAllowed(): bool
    {
        return $this->allowRedirects;
    }

    /**
     * Option to disable SSL certificate verification
     */
    public function optDisableSslVerification(): void
    {
        $this->verifySsl = false;
    }

    /**
     * Is SSL certificate verification enabled
     */
    public function isSslVerificationEnabled(): bool
    {
        return $this->verifySsl;
    }

    /**
     * Option to set a custom CA bundle certificates path. As default we use the CA bundle
     * provided by the operating system.
     */
    public function optSetCustomCaBundlePath(string $customCaBundlePath): void
    {
        $this->customCaBundlePath = $customCaBundlePath;
    }

    /**
     * Returns the custom CA bundle path or an empty string (Default)
     */
    public function getCustomCaBundlePath(): string
    {
        return $this->customCaBundlePath;
    }

    /**
     * Option to set the timeout in seconds for requests
     */
    public function optSetTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Returns the connect timeout
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Option to set the amount of maximal allowed redirects
     */
    public function optSetMaxRedirects(int $maxRedirects): void
    {
        $this->maxRedirects = $maxRedirects;
    }

    /**
     * Returns the amount of max allowed redirects
     */
    public function getMaxAllowedRedirects(): int
    {
        return $this->maxRedirects;
    }

    /**
     * Option to disable the referer for redirects
     */
    public function optDisableRefererForRedirects(): void
    {
        $this->addReferer = false;
    }

    /**
     * Is adding of a referee header for redirects enabled.
     */
    public function isRefererForRedirectsEnabled(): bool
    {
        return $this->addReferer;
    }

    /**
     * Has a custom CA bundle path been set?
     */
    public function hasCustomCaBundlePath(): bool
    {
        return !empty($this->getCustomCaBundlePath());
    }
}
