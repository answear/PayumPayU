<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

class OrderCanceledResponse
{
    public function __construct(
        public readonly ResponseStatus $status,
        public readonly string $orderId,
        public readonly ?string $extOrderId = null
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            ResponseStatus::fromResponse($response['status']),
            $response['orderId'],
            $response['extOrderId'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->toArray(),
            'orderId' => $this->orderId,
            'extOrderId' => $this->extOrderId,
        ];
    }
}
