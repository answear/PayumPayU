<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use Payum\Core\Model\Payment;

class Refund extends \Payum\Core\Request\Refund
{
    public ?RefundCreatedResponse $refundCreatedResponse = null;

    public function __construct(
        Payment $model,
        public readonly string $description,
        public readonly ?int $amount,
        public readonly ?string $extCustomerId = null,
        public readonly ?string $extRefundId = null,
    ) {
        parent::__construct($model);
    }
}
