<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests;

use Answear\Payum\Model\PaidForInterface;

class Payment extends \Answear\Payum\Model\Payment
{
    private string $gatewayName;
    private string $orderId;
    private string $configKey;
    private PaidForInterface $paidFor;
    private string $language;

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): void
    {
        $this->gatewayName = $gatewayName;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId ?? null;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getConfigKey(): ?string
    {
        return $this->configKey ?? null;
    }

    public function setConfigKey(string $configKey): void
    {
        $this->configKey = $configKey;
    }

    public function getPaidFor(): PaidForInterface
    {
        return $this->paidFor;
    }

    public function setPaidFor(PaidForInterface $paidFor): void
    {
        $this->paidFor = $paidFor;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }
}
