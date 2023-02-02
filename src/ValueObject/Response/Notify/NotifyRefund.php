<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\Notify;

use Answear\Payum\PayU\Enum\RefundStatus;
use Webmozart\Assert\Assert;

class NotifyRefund
{
    public function __construct(
        public readonly string $refundId,
        public readonly int $amount,
        public readonly string $currencyCode,
        public readonly RefundStatus $status,
        public readonly \DateTimeImmutable $statusDateTime,
        public readonly string $reason,
        public readonly string $reasonDescription,
        public readonly \DateTimeImmutable $refundDate,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        Assert::numeric($response['amount']);

        return new self(
            $response['refundId'],
            (int) $response['amount'],
            $response['currencyCode'],
            RefundStatus::from($response['status']),
            new \DateTimeImmutable($response['statusDateTime']),
            $response['reason'],
            $response['reasonDescription'],
            new \DateTimeImmutable($response['refundDate']),
        );
    }
}
