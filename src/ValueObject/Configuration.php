<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

readonly class Configuration
{
    public function __construct(
        public string $publicShopId,
        public string $posId,
        public string $signatureKey,
        public string $oauthClientId,
        public string $oauthClientSecret,
    ) {
    }
}
