<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\Notify\NotifyRefund;

class NotifyRefundResponse
{
    public function __construct(
        public readonly string $orderId,
        public readonly ?string $extOrderId,
        public readonly NotifyRefund $refund,
    ) {
    }

    public static function fromResponse(array $content): self
    {
        return new self(
            $content['orderId'],
            $content['extOrderId'] ?? null,
            NotifyRefund::fromResponse($content['refund'])
        );
    }
}
