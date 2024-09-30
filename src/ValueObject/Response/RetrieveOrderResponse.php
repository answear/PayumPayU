<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

readonly class RetrieveOrderResponse
{
    public function __construct(
        public string $orderId,
        public Refund $refund,
        public ResponseStatus $status,
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
