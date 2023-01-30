<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\RefundCreated;

use Answear\Payum\PayU\Enum\RefundStatus;

class Refund
{
    public function __construct(
        public readonly string $refundId,
        public readonly string $extRefundId,
        public readonly int $amount,
        public readonly string $currencyCode,
        public readonly string $description,
        public readonly \DateTimeImmutable $creationDateTime,
        public readonly RefundStatus $status,
        public readonly \DateTimeImmutable $statusDateTime,
    ) {
    }
}