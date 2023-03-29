<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

class Configuration
{
    public function __construct(
        public readonly string $publicShopId,
        public readonly string $posId,
        public readonly string $signatureKey,
        public readonly string $oauthClientId,
        public readonly string $oauthClientSecret,
    ) {
    }
}
