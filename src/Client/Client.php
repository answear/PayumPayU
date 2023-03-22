<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Client;

use Answear\Payum\PayU\Authorization\AuthType\AuthType as AuthorizationAuthType;
use Answear\Payum\PayU\Authorization\AuthType\Basic;
use Answear\Payum\PayU\Authorization\AuthType\Oauth;
use Answear\Payum\PayU\Authorization\AuthType\TokenRequest;
use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Enum\OauthGrantType;
use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\ValueObject\Auth\OauthResultClientCredentials;
use Answear\Payum\PayU\ValueObject\Configuration;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request as HttpRequest;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private const METHOD_POST = 'POST';

    private ?OauthResultClientCredentials $clientCredentials = null;

    public function __construct(
        private ConfigProvider $configProvider,
        private ?ClientInterface $client = null
    ) {
        $this->client = $client ?? new \GuzzleHttp\Client();
    }

    public function getAuthorizeHeaders(
        AuthType $authType,
        ?string $configKey,
        ?string $email = null,
        ?string $extCustomerId = null
    ): AuthorizationAuthType {
        $config = $this->configProvider->getConfig($configKey);

        if (AuthType::OAuthClientCredentials === $authType) {
            $accessToken = $this->retrieveAccessToken(OauthGrantType::ClientCredential, $config);

            return new Oauth($accessToken);
        }

        if (AuthType::OAuthClientTrustedMerchant === $authType) {
            $accessToken = $this->retrieveAccessToken(OauthGrantType::ClientCredential, $config, $email, $extCustomerId);

            return new Oauth($accessToken);
        }

        return new Basic($config->posId, $config->signatureKey);
    }

    private function retrieveAccessToken(
        OauthGrantType $oauthGrantType,
        Configuration $configuration,
        ?string $email = null,
        ?string $extCustomerId = null
    ): string {
        if ($this->hasValidAccessToken($oauthGrantType)) {
            return $this->clientCredentials->accessToken;
        }

        $oauthUrl = $this->configProvider->getOAuthEndpoint();
        $data = [
            'grant_type' => $oauthGrantType->value,
            'client_id' => $configuration->oauthClientId,
            'client_secret' => $configuration->oauthClientSecret,
        ];

        if (OauthGrantType::TrustedMerchant === $oauthGrantType) {
            $data['email'] = $email;
            $data['ext_customer_id'] = $extCustomerId;
        }

        $this->clientCredentials = OauthResultClientCredentials::fromResponse(
            $this->tokenRequest(self::METHOD_POST, $oauthUrl, $data)
        );

        return $this->clientCredentials->accessToken;
    }

    public function payuRequest(string $method, string $endpoint, AuthorizationAuthType $authType, ?array $data = null): ResponseInterface
    {
        $pathUrl = $this->configProvider->getServiceUrl() . $endpoint;
        $psrRequest = new HttpRequest(
            $method,
            new Uri($pathUrl),
            $authType->getHeaders(),
            'GET' === $method
                ? null
                : json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $psrResponse = $this->client->send($psrRequest);

        if ($psrResponse->getBody()->isSeekable()) {
            $psrResponse->getBody()->rewind();
        }

        return $psrResponse;
    }

    private function tokenRequest(string $method, string $pathUrl, ?array $data = null): ResponseInterface
    {
        $psrRequest = new HttpRequest(
            $method,
            new Uri($pathUrl),
            (new TokenRequest())->getHeaders(),
            'GET' === $method ? null : http_build_query($data, '', '&')
        );

        $psrResponse = $this->client->send($psrRequest);

        if ($psrResponse->getBody()->isSeekable()) {
            $psrResponse->getBody()->rewind();
        }

        return $psrResponse;
    }

    private function hasValidAccessToken(OauthGrantType $oauthGrantType): bool
    {
        if (!isset($this->clientCredentials) || $this->clientCredentials->hasExpire()) {
            return false;
        }

        if (OauthGrantType::TrustedMerchant === $oauthGrantType || OauthGrantType::TrustedMerchant === $this->clientCredentials->grantType) {
            return false;
        }

        return $this->clientCredentials->grantType === $oauthGrantType;
    }
}
