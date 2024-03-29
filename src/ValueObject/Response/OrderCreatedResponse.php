<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\OrderCreated\OrderCreatedStatus;

class OrderCreatedResponse
{
    public function __construct(
        public readonly OrderCreatedStatus $status,
        public readonly ?string $redirectUri,
        public readonly string $orderId,
        public readonly ?string $extOrderId = null,
        public readonly ?array $payMethods = null,
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
