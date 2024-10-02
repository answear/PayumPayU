<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\Notify;

use Answear\Payum\PayU\Enum\RefundStatus;
use Webmozart\Assert\Assert;

readonly class NotifyRefund
{
    public function __construct(
        public string $refundId,
        public ?string $extRefundId,
        public int $amount,
        public string $currencyCode,
        public RefundStatus $status,
        public \DateTimeImmutable $statusDateTime,
        public string $reason,
        public string $reasonDescription,
        public \DateTimeImmutable $refundDate,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        Assert::numeric($response['amount']);

        return new self(
            $response['refundId'],
            $response['extRefundId'] ?? null,
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
