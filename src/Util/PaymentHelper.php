<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

use Answear\Payum\PayU\Model\Model;
use Payum\Core\Model\Payment;

class PaymentHelper
{
    public static function getPaymentOrNull($payment): ?Payment
    {
        return $payment instanceof Payment ? $payment : null;
    }

    public static function getOrderId(Model $model, ?Payment $firstModel): ?string
    {
        $orderId = null;
        if ($firstModel instanceof \Answear\Payum\Model\Payment) {
            $orderId = $firstModel->getOrderId();
        }

        return $orderId ?? $model->orderId();
    }

    public static function getConfigKey(Model $model, ?Payment $firstModel): ?string
    {
        $configKey = null;
        if ($firstModel instanceof \Answear\Payum\Model\Payment) {
            $configKey = $firstModel->getConfigKey();
        }

        return $configKey ?? $model->configKey();
    }
}
