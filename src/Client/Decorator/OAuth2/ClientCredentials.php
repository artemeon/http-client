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

namespace Artemeon\HttpClient\Client\Decorator\OAuth2;

/**
 * Class to generate client credentials for OAuth2 Access Token Request's
 */
class ClientCredentials
{
    private string $clientId;
    private string $clientSecret;
    private string $scope;

    /**
     * ClientCredentials constructor.
     *
     * @param string $clientID The 'client_id'
     * @param string $clientSecret The 'client_secret'
     * @param string $scope The 'scope'
     */
    private function __construct(string $clientID, string $clientSecret, string $scope = '')
    {
        // According to the rfc https://tools.ietf.org/html/rfc6749#page-43 encoding must UTF-8
        $this->clientId = mb_convert_encoding($clientID, 'UTF-8', 'ISO-8859-1');
        $this->clientSecret = mb_convert_encoding($clientSecret, 'UTF-8', 'ISO-8859-1');
        $this->scope = mb_convert_encoding($scope, 'UTF-8', 'ISO-8859-1');
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Named constructor to create an instance based on the given credentials
     *
     * @param string $clientId  The 'client_id'
     * @param string $clientSecret The 'client_secret'
     * @param string $scope The scope
     */
    public static function forHeaderAuthorization(string $clientId, string $clientSecret, string $scope = ''): self
    {
        return new self($clientId, $clientSecret, $scope);
    }

    /**
     * Creates the required key => value pairs for the Access Token Request
     */
    public function toArray(bool $includeClientCredentials = true): array
    {
        $requestData = [
            'grant_type' => 'client_credentials',
        ];

        if ($includeClientCredentials) {
            $requestData['client_id'] = $this->clientId;
            $requestData['client_secret'] = $this->clientSecret;
        }

        if (!empty($this->scope)) {
            $requestData['scope'] = $this->scope;
        }

        return $requestData;
    }
}
