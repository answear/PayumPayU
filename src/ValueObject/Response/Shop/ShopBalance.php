<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\Shop;

readonly class ShopBalance
{
    public function __construct(
        public string $currencyCode,
        public int $total,
        public int $available,
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
