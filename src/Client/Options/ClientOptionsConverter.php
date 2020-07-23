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

use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

/**
 * Class to convert http-client options object to the guzzle options array format
 */
class ClientOptionsConverter
{
    /**
     * Converts the given ClientOptions to the guzzle options array format
     *
     * @param ClientOptions $clientOptions
     */
    public function toGuzzleOptionsArray(ClientOptions $clientOptions): array
    {
        $options = [];

        $options[GuzzleRequestOptions::VERIFY] = $this->createVerifyKey($clientOptions);
        $options[GuzzleRequestOptions::ALLOW_REDIRECTS] = $this->createAllowRedirectsKey($clientOptions);
        $options[GuzzleRequestOptions::TIMEOUT] = $clientOptions->getTimeout();

        if ($clientOptions->getSink() !== null) {
            $options[GuzzleRequestOptions::SINK] = $clientOptions->getSink();
        }

        return $options;
    }

    /**
     * @see http://docs.guzzlephp.org/en/6.5/request-options.html#verify
     * @param ClientOptions $clientOptions
     * @return string|bool
     */
    private function createVerifyKey(ClientOptions $clientOptions)
    {
        if ($clientOptions->isSslVerificationEnabled()) {
            if ($clientOptions->hasCustomCaBundlePath()) {
                return $clientOptions->getCustomCaBundlePath();
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * @see http://docs.guzzlephp.org/en/6.5/request-options.html#allow-redirects
     * @param ClientOptions $clientOptions
     * @return array|bool
     */
    private function createAllowRedirectsKey(ClientOptions $clientOptions)
    {
        if ($clientOptions->isRedirectAllowed()) {
            return [
                'max' => $clientOptions->getMaxAllowedRedirects(),
                'referer' => $clientOptions->isRefererForRedirectsEnabled(),
            ];
        }
        return false;
    }
}
