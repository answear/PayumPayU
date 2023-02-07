<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Model;

abstract class Payment extends \Payum\Core\Model\Payment
{
    abstract public function getGatewayName(): string;

    abstract public function getOrderId(): ?string;

    abstract public function setOrderId(string $orderId): void;

    abstract public function getConfigKey(): ?string;

    abstract public function getPaidFor(): PaidForInterface;

    abstract public function getLanguage(): string;
}
