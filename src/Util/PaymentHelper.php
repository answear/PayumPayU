<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

use Payum\Core\Model\Payment;

class PaymentHelper
{
    public static function getPaymentOrNull($payment): ?Payment
    {
        return $payment instanceof Payment ? $payment : null;
    }
}
