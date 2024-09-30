<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\PayMethod;
use Answear\Payum\PayU\ValueObject\Product;
use Webmozart\Assert\Assert;

readonly class Order
{
    public const DEFAULT_VALIDITY_TIME = 86400;

    public function __construct(
        public string $orderId,
        public ?string $extOrderId,
        public \DateTimeImmutable $orderCreateDate,
        public string $notifyUrl,
        public string $customerIp,
        public string $merchantPosId,
        public string $description,
        public ?string $additionalDescription,
        public string $currencyCode,
        public int $totalAmount,
        public array $products,
        public OrderStatus $status,
        public ?Buyer $buyer = null,
        public ?PayMethod $payMethod = null,
        public ?int $validityTime = self::DEFAULT_VALIDITY_TIME,
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
