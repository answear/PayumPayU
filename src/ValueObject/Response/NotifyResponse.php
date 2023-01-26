<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\Notify\NotifyOrder;

class NotifyResponse
{
    public function __construct(
        public readonly NotifyOrder $order,
        public readonly ?\DateTimeImmutable $localReceiptDateTime,
        public readonly array $properties,
    ) {
    }
}
