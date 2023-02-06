<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\CodeLiteral;
use Answear\Payum\PayU\Enum\ResponseStatusCode;

class ResponseStatus
{
    public function __construct(
        public readonly ResponseStatusCode $statusCode,
        public readonly ?string $statusDesc = null,
        public readonly ?string $severity = null,
        public readonly ?string $code = null,
        public readonly ?CodeLiteral $codeLiteral = null,
        public readonly ?string $rawCodeLiteral = null,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            ResponseStatusCode::from($response['statusCode']),
            $response['statusDesc'] ?? null,
            $response['severity'] ?? null,
            $response['code'] ?? null,
            isset($response['codeLiteral']) ? CodeLiteral::tryFrom($response['codeLiteral']) : null,
            $response['codeLiteral'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'statusCode' => $this->statusCode->value,
            'statusDesc' => $this->statusDesc,
            'severity' => $this->severity,
            'code' => $this->code,
            'codeLiteral' => $this->rawCodeLiteral,
        ];
    }
}
