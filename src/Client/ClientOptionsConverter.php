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

namespace Artemeon\HttpClient\Client;

use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

/**
 * Class to convert http-client options object to the guzzle options array format
 */
class ClientOptionsConverter
{
    public function toGuzzleOptionsArray(ClientOptions $clientOptions): array
    {
        $options = [];

        $options[GuzzleRequestOptions::VERIFY] = $this->createVerifyKey($clientOptions);
        $options[GuzzleRequestOptions::ALLOW_REDIRECTS] = $this->createAllowRedirectsKey($clientOptions);
        $options[GuzzleRequestOptions::TIMEOUT] = $clientOptions->getTimeout();

        return $options;
    }

    /**
     * @see http://docs.guzzlephp.org/en/6.5/request-options.html#verify
     * @return array|bool
     */
    private function createVerifyKey(ClientOptions $clientOptions)
    {
        if ($clientOptions->isCertificateVerificationEnabled()) {
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
     * @return array|bool
     */
    private function createAllowRedirectsKey(ClientOptions $clientOptions)
    {
        if ($clientOptions->isRedirectAllowed()) {
            return [
                'max' => $clientOptions->getMaxRedirects(),
                'referer' => $clientOptions->isRefererAllowed(),
            ];
        }
        return false;
    }
}
