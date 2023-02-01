<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\PayMethod;
use Answear\Payum\PayU\ValueObject\Product;
use Webmozart\Assert\Assert;

class Order
{
    public const DEFAULT_VALIDITY_TIME = 86400;

    public function __construct(
        public readonly string $orderId,
        public readonly ?string $extOrderId,
        public readonly \DateTimeImmutable $orderCreateDate,
        public readonly string $notifyUrl,
        public readonly string $customerIp,
        public readonly string $merchantPosId,
        public readonly string $description,
        public readonly ?string $additionalDescription,
        public readonly string $currencyCode,
        public readonly int $totalAmount,
        public readonly array $products,
        public readonly OrderStatus $status,
        public readonly ?Buyer $buyer = null,
        public readonly ?PayMethod $payMethod = null,
        public readonly ?int $validityTime = self::DEFAULT_VALIDITY_TIME,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        Assert::numeric($response['totalAmount']);

        return new self(
            $response['orderId'],
            $response['extOrderId'] ?? null,
            new \DateTimeImmutable($response['orderCreateDate']),
            $response['notifyUrl'],
            $response['customerIp'],
            $response['merchantPosId'],
            $response['description'],
            $response['additionalDescription'] ?? null,
            $response['currencyCode'],
            (int) $response['totalAmount'],
            array_map(
                static fn(array $productData) => Product::fromResponse($productData),
                $response['products']
            ),
            OrderStatus::from($response['status']),
            isset($response['buyer']) ? Buyer::fromResponse($response['buyer']) : null,
            isset($response['payMethod']) ? PayMethod::fromResponse($response['payMethod']) : null,
            (int) ($response['validityTime'] ?? self::DEFAULT_VALIDITY_TIME),
        );
    }
}
