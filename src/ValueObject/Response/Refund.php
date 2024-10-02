<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\RefundStatus;
use Webmozart\Assert\Assert;

readonly class Refund
{
    public function __construct(
        public string $refundId,
        public string $extRefundId,
        public int $amount,
        public string $currencyCode,
        public string $description,
        public \DateTimeImmutable $creationDateTime,
        public ?\DateTimeImmutable $statusDateTime,
        public RefundStatus $status,
        public ?RefundStatusError $statusError = null,
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
            empty($response['statusDateTime']) ? null : new \DateTimeImmutable($response['statusDateTime']),
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
            'statusDateTime' => $this->statusDateTime?->format(\DateTimeInterface::ATOM),
            'status' => $this->status->value,
            'statusError' => $this->statusError?->toArray(),
        ];
    }
}
