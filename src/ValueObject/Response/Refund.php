<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\RefundStatus;
use Webmozart\Assert\Assert;

class Refund
{
    public function __construct(
        public readonly string $refundId,
        public readonly string $extRefundId,
        public readonly int $amount,
        public readonly string $currencyCode,
        public readonly string $description,
        public readonly \DateTimeImmutable $creationDateTime,
        public readonly \DateTimeImmutable $statusDateTime,
        public readonly RefundStatus $status,
        public readonly ?RefundStatusError $statusError = null
    ) {
    }

    public static function fromResponse(array $response): self
    {
        Assert::numeric($response['amount']);

        return new self(
            $response['refundId'],
            $response['extRefundId'],
            (int) $response['amount'],
            $response['currencyCode'],
            $response['description'],
            new \DateTimeImmutable($response['creationDateTime']),
            new \DateTimeImmutable($response['statusDateTime']),
            RefundStatus::from($response['status']),
            isset($response['statusError']) ? RefundStatusError::fromResponse($response['statusError']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'refundId' => $this->refundId,
            'extRefundId' => $this->extRefundId,
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode,
            'description' => $this->description,
            'creationDateTime' => $this->creationDateTime->format(\DateTimeInterface::ATOM),
            'statusDateTime' => $this->statusDateTime->format(\DateTimeInterface::ATOM),
            'status' => $this->status->value,
            'statusError' => $this->statusError?->toArray(),
        ];
    }
}
