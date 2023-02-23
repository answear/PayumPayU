<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\PaymentInterface;

class SyncPayment implements ModelAggregateInterface
{
    public function __construct(private readonly PaymentInterface $model)
    {
    }

    public function getModel(): PaymentInterface
    {
        return $this->model;
    }
}
