<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Refund;

class Refund
{
    public function __construct(
        public readonly string $description,
        public readonly ?string $amount,
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
        ];
    }
}
