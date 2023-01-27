<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\ValueObject\Response\RefundCreated\StatusCode;

class ResponseStatus
{
    public function __construct(
        public readonly StatusCode $statusCode,
        public readonly ?string $statusDesc = null,
        public readonly ?string $severity = null,
        public readonly ?string $code = null,
        public readonly ?string $codeLiteral = null,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            StatusCode::from($response['statusCode']),
            $response['statusDesc'] ?? null,
            $response['severity'] ?? null,
            $response['code'] ?? null,
            $response['codeLiteral'] ?? null,
        );
    }
}
