<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\RefundStatusErrorCode;

class RefundStatusError
{
    public function __construct(
        public readonly RefundStatusErrorCode $code,
        public readonly string $rawCode,
        public readonly string $description,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            RefundStatusErrorCode::tryFrom($response['code']) ?? RefundStatusErrorCode::Unknown,
            $response['code'] ?? '',
            $response['description']
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->rawCode,
            'description' => $this->description,
        ];
    }
}
