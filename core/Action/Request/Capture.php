<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Security\TokenInterface;

class Capture extends \Payum\Core\Request\Capture
{
    public function __construct(
        TokenInterface $captureToken,
        PaymentInterface $payment,
        public readonly ?PayMethod $payMethod = null,
    ) {
        parent::__construct($captureToken);

        $this->setModel($payment);
        $this->setModel($payment->getDetails());
    }
}
