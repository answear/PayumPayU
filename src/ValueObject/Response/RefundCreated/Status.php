<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\RefundCreated;

class Status
{
    public function __construct(
        public readonly StatusCode $statusCode,
        public readonly ?string $statusDesc,
        public readonly ?string $severity,
        public readonly ?string $code,
        public readonly ?string $codeLiteral,
    ) {
    }
}
