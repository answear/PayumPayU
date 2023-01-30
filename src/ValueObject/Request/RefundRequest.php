<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request;

use Answear\Payum\PayU\ValueObject\Request\Refund\Refund;

class RefundRequest
{
    public function __construct(
        public readonly Refund $refund
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'refund' => $this->refund->toArray(),
        ];
    }
}
