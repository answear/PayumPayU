<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\Notify;

use Answear\Payum\PayU\Enum\OrderStatus;

class NotifyOrder
{
    public function __construct(
        public readonly string $orderId,
        public readonly ?string $extOrderId,
        public readonly \DateTimeImmutable $orderCreateDate,
        public readonly string $notifyUrl,
        public readonly string $customerIp,
        public readonly string $merchantPosId,
        public readonly string $description,
        public readonly int $totalAmount,
        public readonly array $products,
        public readonly OrderStatus $status
    ) {
    }
}
