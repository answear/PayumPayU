<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

class RefundCreatedResponse
{
    public function __construct(
        public readonly string $orderId,
        public readonly Refund $refund,
        public readonly ResponseStatus $status,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['orderId'],
            Refund::fromResponse($response['refund']),
            ResponseStatus::fromResponse($response['status']),
        );
    }
}
