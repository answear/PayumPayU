<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderTransactions;

use Answear\Payum\PayU\ValueObject\PayMethod;

interface OrderRetrieveTransactionsResponseInterface
{
    public function getPayMethod(): PayMethod;
}
