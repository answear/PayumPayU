<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum AuthType: string
{
    case Basic = 'Basic';
    case OAuthClientCredentials = 'OAuthClientCredentials';
    case OAuthClientTrustedMerchant = 'OAuthTrustedMerchant';
}
