<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

use Answear\Payum\Model\Payment;
use Answear\Payum\PayU\Model\Model;
use Payum\Core\Model\PaymentInterface;

class PaymentHelper
{
    public static function getPaymentOrNull($payment): ?PaymentInterface
    {
        return $payment instanceof PaymentInterface ? $payment : null;
    }

    public static function getOrderId(Model $model, ?PaymentInterface $firstModel): ?string
    {
        $orderId = null;
        if ($firstModel instanceof Payment) {
            $orderId = $firstModel->getOrderId();
        }

        return $orderId ?? $model->orderId();
    }

    public static function getConfigKey(Model $model, ?PaymentInterface $firstModel): ?string
    {
        $configKey = null;
        if ($firstModel instanceof Payment) {
            $configKey = $firstModel->getConfigKey();
        }

        return $configKey ?? $model->configKey();
    }
}
