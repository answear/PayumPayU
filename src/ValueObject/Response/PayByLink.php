<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\PayByLinkStatus;

readonly class PayByLink
{
    private const AMOUNT_MIN = 0;
    private const AMOUNT_MAX = 99999999;

    public function __construct(
        public string $value,
        public string $name,
        public string $brandImageUrl,
        public PayByLinkStatus $status,
        public ?int $minAmount = null,
        public ?int $maxAmount = null,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['value'],
            $response['name'],
            $response['brandImageUrl'],
            PayByLinkStatus::from($response['status']),
            (!isset($response['minAmount']) || self::AMOUNT_MIN === $response['minAmount']) ? null : $response['minAmount'],
            (!isset($response['maxAmount']) || self::AMOUNT_MAX === $response['maxAmount']) ? null : $response['maxAmount'],
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->name,
            'brandImageUrl' => $this->brandImageUrl,
            'status' => $this->status->value,
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
        ];
    }
}
