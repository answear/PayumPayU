<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Refund;

readonly class Refund
{
    public function __construct(
        public string $description,
        public ?int $amount,
        public ?string $extCustomerId = null,
        public ?string $extRefundId = null,
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'amount' => $this->amount,
            'extCustomerId' => $this->extCustomerId,
            'extRefundId' => $this->extRefundId,
        ];
    }
}
