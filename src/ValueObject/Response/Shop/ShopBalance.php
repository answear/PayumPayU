<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\Shop;

class ShopBalance
{
    public function __construct(
        public readonly string $currencyCode,
        public readonly int $total,
        public readonly int $available
    ) {
    }

    public static function fromResponse(array $shopBalanceResponse): self
    {
        return new self(
            $shopBalanceResponse['currencyCode'],
            (int) $shopBalanceResponse['total'],
            (int) $shopBalanceResponse['available']
        );
    }
}
