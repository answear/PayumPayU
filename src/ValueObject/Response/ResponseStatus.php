<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\CodeLiteral;
use Answear\Payum\PayU\Enum\ResponseStatusCode;

readonly class ResponseStatus
{
    public function __construct(
        public ResponseStatusCode $statusCode,
        public ?string $statusDesc = null,
        public ?string $severity = null,
        public ?string $code = null,
        public ?CodeLiteral $codeLiteral = null,
        public ?string $rawCodeLiteral = null,
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
