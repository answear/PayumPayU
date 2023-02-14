<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\Shop\ShopBalance;

class ShopInfo
{
    public function __construct(
        public readonly string $shopId,
        public readonly string $name,
        public readonly string $currencyCode,
        public readonly ShopBalance $balance
    ) {
    }

    public static function fromPayUShop(\PayuShop $payuShop): self
    {
        return new self(
            $payuShop->getShopId(),
            $payuShop->getName(),
            $payuShop->getCurrencyCode(),
            ShopBalance::fromPayUShopBalance($payuShop->getBalance()),
        );
    }
}
