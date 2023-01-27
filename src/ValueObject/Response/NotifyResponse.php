<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

class NotifyResponse
{
    public function __construct(
        public readonly Order $order,
        public readonly ?\DateTimeImmutable $localReceiptDateTime,
        public readonly array $properties,
    ) {
    }
}
