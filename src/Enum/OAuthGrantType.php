<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum OAuthGrantType: string
{
    case ClientCredential = 'client_credentials';
    case TrustedMerchant = 'trusted_merchant';
}
