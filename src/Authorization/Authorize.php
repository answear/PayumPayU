<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Authorization;

use Answear\Payum\PayU\ValueObject\Configuration;

class Authorize
{
    public static function base(Configuration $configuration): void
    {
        self::clear();
        \OpenPayU_Configuration::setEnvironment($configuration->environment->value);
        \OpenPayU_Configuration::setMerchantPosId($configuration->posId);
        \OpenPayU_Configuration::setSignatureKey($configuration->signatureKey);
    }

    public static function withTrusted(Configuration $configuration, string $userId, string $userEmail): void
    {
        self::base($configuration);

        \OpenPayU_Configuration::setOauthClientId($configuration->oauthClientId);
        \OpenPayU_Configuration::setOauthClientSecret($configuration->oauthClientId);
        \OpenPayU_Configuration::setOauthGrantType(\OauthGrantType::TRUSTED_MERCHANT);
        \OpenPayU_Configuration::setOauthEmail($userEmail);
        \OpenPayU_Configuration::setOauthExtCustomerId($userId);
    }

    private static function clear(): void
    {
        \OpenPayU_Configuration::setEnvironment();
        \OpenPayU_Configuration::setMerchantPosId('');
        \OpenPayU_Configuration::setSignatureKey('');
        \OpenPayU_Configuration::setOauthClientId('');
        \OpenPayU_Configuration::setOauthClientSecret('');
        \OpenPayU_Configuration::setOauthGrantType(\OauthGrantType::CLIENT_CREDENTIAL);
        \OpenPayU_Configuration::setOauthEmail('');
        \OpenPayU_Configuration::setOauthExtCustomerId('');
    }
}
