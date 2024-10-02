<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

readonly class OrderCanceledResponse
{
    public function __construct(
        public ResponseStatus $status,
        public string $orderId,
        public ?string $extOrderId = null,
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
