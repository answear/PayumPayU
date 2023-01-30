<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\OrderCreated\OrderCreatedStatus;

class OrderCreatedResponse
{
    public function __construct(
        public readonly OrderCreatedStatus $status,
        public readonly string $redirectUri,
        public readonly string $orderId,
        public readonly ?string $extOrderId,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            OrderCreatedStatus::fromResponse($response['status']),
            $response['redirectUri'],
            $response['orderId'],
            $response['extOrderId'] ?? null,
        );
    }
}
