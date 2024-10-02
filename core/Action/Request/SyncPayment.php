<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\PaymentInterface;

readonly class SyncPayment implements ModelAggregateInterface
{
    public function __construct(private PaymentInterface $model)
    {
    }

    public function getModel(): PaymentInterface
    {
        return $this->model;
    }
}
