<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\OrderCreated\OrderCreatedStatus;

readonly class OrderCreatedResponse
{
    public function __construct(
        public OrderCreatedStatus $status,
        public ?string $redirectUri,
        public string $orderId,
        public ?string $extOrderId = null,
        public ?array $payMethods = null,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            OrderCreatedStatus::fromResponse($response['status']),
            $response['redirectUri'] ?? null,
            $response['orderId'],
            $response['extOrderId'] ?? null,
            $response['payMethods'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->toArray(),
            'redirectUri' => $this->redirectUri,
            'orderId' => $this->orderId,
            'extOrderId' => $this->extOrderId,
            'payMethods' => $this->payMethods,
        ];
    }
}
