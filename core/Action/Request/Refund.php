<?php

declare(strict_types=1);

namespace Answear\Payum\Action\Request;

use Payum\Core\Model\PaymentInterface;

class Refund extends \Payum\Core\Request\Refund
{
    public ?object $refundCreatedResponse = null;

    public function __construct(
        PaymentInterface $model,
        public readonly string $description,
        public readonly ?int $amount,
        public readonly ?string $extCustomerId = null,
        public readonly ?string $extRefundId = null,
    ) {
        parent::__construct($model);
    }
}
