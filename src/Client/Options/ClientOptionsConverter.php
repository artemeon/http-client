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
 * Class to convert http-client options object to the guzzle options array format.
 */
class ClientOptionsConverter
{
    /**
     * Converts the given ClientOptions to the guzzle options array format.
     */
    public function toGuzzleOptionsArray(ClientOptions $clientOptions): array
    {
        $options = [];

        $options[GuzzleRequestOptions::VERIFY] = $this->createVerifyKey($clientOptions);
        $options[GuzzleRequestOptions::ALLOW_REDIRECTS] = $this->createAllowRedirectsKey($clientOptions);
        $options[GuzzleRequestOptions::TIMEOUT] = $clientOptions->getTimeout();
        $options[GuzzleRequestOptions::HTTP_ERRORS] = !$clientOptions->isNonSuccessfulHttpStatusAllowed();

        if ($clientOptions->getSink() !== null) {
            $options[GuzzleRequestOptions::SINK] = $clientOptions->getSink();
        }
        if ($clientOptions->getHandler() !== null) {
            $options['handler'] = $clientOptions->getHandler();
        }

        return $options;
    }

    /**
     * @see http://docs.guzzlephp.org/en/6.5/request-options.html#verify
     * @return string|bool
     */
    private function createVerifyKey(ClientOptions $clientOptions)
    {
        if ($clientOptions->isSslVerificationEnabled()) {
            if ($clientOptions->hasCustomCaBundlePath()) {
                return $clientOptions->getCustomCaBundlePath();
            }

            return true;
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
                'max' => $clientOptions->getMaxAllowedRedirects(),
                'referer' => $clientOptions->isRefererForRedirectsEnabled(),
            ];
        }

        return false;
    }
}
