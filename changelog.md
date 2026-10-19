## 1.1.3 - 2026-10-19
- Fixed: The `ClientCredentialsDecorator` accepts an access token response whose content type carries parameters, e.g. `application/json;charset=UTF-8` (previously rejected with "Content type should be: application/json").
- Fixed: The exception for an invalid status code of the access token request now contains the status code.
- Fixed: The `ClientCredentialsDecorator` passes the client options of a request (e.g. timeout, proxy, TLS) to the access token request as well.

## 1.1.2 - 2026-02-18
- Widen the Guzzle dependency range from `~7.9.2` to `^7.9.2`.

## 1.1.1 - 2025-07-03
- Added the option `strictRfc` to `ClientCredentialsDecorator::fromClientCredentials()`: it sends the client id and secret only in the authorization header and removes them from the request body.

## 1.1.0 - 2025-05-30
- Add support for `psr/log` 2 and 3.

## 1.0.1 - 2025-02-04
- Remove the version from `composer.json`.

## 1.0.0 - 2025-01-20
- Drop support for PHP 8.1, PHP 8.2 and PHP 8.3.
- Add Support for PHP 8.4.

## 0.2.7 - 2025-06-04
- Added the option `strictRfc` to `ClientCredentialsDecorator::fromClientCredentials()`: it sends the client id and secret only in the authorization header and removes them from the request body.

## 0.2.6 - 2025-02-04
- Add support for `psr/http-message` 2.

## 0.2.5 - 2025-02-02
- Require `psr/http-message` 1 because of incompatibilities with version 2.

## 0.2.4 - 2024-10-24
- Replace the deprecated `utf8_encode()` in `ClientCredentials`.
- Fixed `ResponseException::fromResponse()` and `TransferException::fromRequest()` to create the exception class they are called on.
- Added PHPStan.

## 0.2.3 - 2023-07-28
- Added a readme and updated the license.
- Update `guzzlehttp/psr7` to 2.5.0.

## 0.2.2 - 2023-01-25
- Fixed null response in exception handling

## 0.2.1 - 2022-10-12
- Added optional parameter to set custom mime type for json encoder
- Added media type constant for application/vnd.api+json

## 0.2.0 - 2022-08-02
- Update to php 8.1
- Update to guzzle 7.4.5
- Fixed incorrect test namespaces
- Added psalm support

## 0.1.5 - 2023-02-07
- Fixed null response in `ResponseException`.

## 0.1.4 - 2022-10-13
- Removed `ext-http` from `composer.json`.

## 0.1.3 - 2022-10-13
- Backport of 0.2.1: optional parameter to set a custom mime type for the json encoder and the media type constant for application/vnd.api+json.

## 0.1.2 - 2021-07-13
- Fixed null response in exception handling
- Added ClientOptionsModifier classes
- Added type hints for properties

## 0.1.1 - 2020-07-23
- Added client option 'sink' (GuzzleRequestOptions::SINK)

## 0.1.0 - 2020-02-12
- Initial release
