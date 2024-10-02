<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderTransactions;

use Answear\Payum\PayU\ValueObject\PayMethod;

readonly class ByCreditCard implements OrderRetrieveTransactionsResponseInterface
{
    public function __construct(
        public PayMethod $payMethod,
        public string $paymentFlow,
        public ?array $card,
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
            $response['paymentFlow'],
            $response['card'] ?? null,
        );
    }
}
