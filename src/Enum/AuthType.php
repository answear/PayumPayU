<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum AuthType: string
{
    case Base = 'base';
    case OAuthClientCredentials = 'OAuthClientCredentials';
    case OAuthClientTrustedMerchant = 'OAuthTrustedMerchant';
}
