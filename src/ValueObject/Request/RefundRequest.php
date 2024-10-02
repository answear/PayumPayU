<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request;

use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\ValueObject\Request\Refund\Refund;

readonly class RefundRequest
{
    public const METHOD = 'POST';
    public const AUTH_TYPE = AuthType::Basic;

    public function __construct(
        public Refund $refund,
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
