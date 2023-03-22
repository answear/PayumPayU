<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Client;

use Answear\Payum\PayU\Authorization\AuthType\AuthType as AuthorizationAuthType;
use Answear\Payum\PayU\Authorization\AuthType\Basic;
use Answear\Payum\PayU\Authorization\AuthType\Oauth;
use Answear\Payum\PayU\Authorization\AuthType\TokenRequest;
use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Enum\Environment;
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
        Environment $environment,
        AuthType $authType,
        ?string $configKey,
        ?string $email = null,
        ?string $extCustomerId = null
    ): array {
        $config = $this->configProvider->getConfig($configKey);

        if (AuthType::OAuthClientCredentials === $authType) {
            $accessToken = $this->retrieveAccessToken($environment, OauthGrantType::ClientCredential, $config);

            return (new Oauth($accessToken))->getHeaders();
        }

        if (AuthType::OAuthClientTrustedMerchant === $authType) {
            $accessToken = $this->retrieveAccessToken($environment, OauthGrantType::ClientCredential, $config, $email, $extCustomerId);

            return (new Oauth($accessToken))->getHeaders();
        }

        return (new Basic($config->posId, $config->signatureKey))->getHeaders();
    }

    private function retrieveAccessToken(
        Environment $environment,
        OauthGrantType $oauthGrantType,
        Configuration $configuration,
        ?string $email = null,
        ?string $extCustomerId = null
    ): string {
        if ($this->hasValidAccessToken($oauthGrantType)) {
            return $this->clientCredentials->accessToken;
        }

        $authType = new TokenRequest();

        $oauthUrl = $this->configProvider->getOAuthEndpoint($environment);
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
            $this->request(self::METHOD_POST, $oauthUrl, $authType, $data)
        );

        return $this->clientCredentials->accessToken;
    }

    private function request(string $method, string $pathUrl, AuthorizationAuthType $auth, ?array $data = null): ResponseInterface
    {
        $psrRequest = new HttpRequest(
            $method,
            new Uri($pathUrl),
            $auth->getHeaders(),
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
