<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class SyncPayment implements ModelAggregateInterface
{
    private PaymentInterface $model;

    public function __construct(PaymentInterface $model)
    {
        $this->setModel($model);
    }

    public function getModel(): PaymentInterface
    {
        return $this->model;
    }

    /**
     * @param PaymentInterface $model
     */
    public function setModel($model): void
    {
        Assert::isInstanceOf($model, PaymentInterface::class);
        $this->model = $model;
    }
}
