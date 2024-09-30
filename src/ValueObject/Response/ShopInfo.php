<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\Shop\ShopBalance;

readonly class ShopInfo
{
    public function __construct(
        public string $shopId,
        public string $name,
        public string $currencyCode,
        public ShopBalance $balance,
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
