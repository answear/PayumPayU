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

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['shopId'],
            $response['name'],
            $response['currencyCode'],
            ShopBalance::fromResponse($response['balance']),
        );
    }
}
