<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderTransactions;

use Answear\Payum\PayU\ValueObject\PayMethod;

class ByCreditCard implements OrderRetrieveTransactionsResponseInterface
{
    public function __construct(
        public readonly PayMethod $payMethod,
        public readonly string $paymentFlow,
        public readonly CardData $cardData,
        public readonly array $cardInstallmentProposal,
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
            CardData::fromResponse($response['card']['cardData']),
            $response['cardInstallmentProposal'],
        );
    }
}
