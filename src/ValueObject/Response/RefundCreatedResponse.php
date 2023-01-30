<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\RefundCreated\Refund;
use Answear\Payum\PayU\ValueObject\Response\RefundCreated\Status;

class RefundCreatedResponse
{
    public function __construct(
        public readonly string $orderId,
        public readonly Refund $refund,
        public readonly Status $status,
    ) {
    }
}
