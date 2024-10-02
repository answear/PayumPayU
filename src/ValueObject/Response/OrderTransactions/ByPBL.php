<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderTransactions;

use Answear\Payum\PayU\ValueObject\PayMethod;

readonly class ByPBL implements OrderRetrieveTransactionsResponseInterface
{
    public function __construct(
        public PayMethod $payMethod,
        public ?array $bankAccount,
    ) {
    }

    public function getPayMethod(): PayMethod
    {
        return $this->payMethod;
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            PayMethod::fromResponse($response['payMethod']),
            $response['bankAccount'] ?? null,
        );
    }
}
