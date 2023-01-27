<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Authorization\Authorize;
use Answear\Payum\PayU\Request\Order;
use Answear\Payum\PayU\Request\Refund;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use Payum\Core\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert;

class Api
{
    private ?string $defaultConfigKey = null;

    /**
     * @param array<string, Configuration> $configurations
     */
    public function __construct(protected array $configurations)
    {
        try {
            Assert::allIsInstanceOf($configurations, Configuration::class);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        if (1 === \count($configurations)) {
            $this->defaultConfigKey = array_key_first($configurations);
        }
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws \OpenPayU_Exception
     */
    public function createOrder(OrderRequest $orderRequest, ?string $configKey = null): OrderCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Order::create($orderRequest);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws \OpenPayU_Exception
     */
    public function createRefund(string $orderId, RefundRequest $refundRequest, ?string $configKey = null): RefundCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Refund::create($orderId, $refundRequest);
    }

    /**
     * @see OrderCreatedResponse::$orderId
     */
    public function retrieveOrder(string $orderId, ?string $configKey = null): OrderRetrieveResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Order::retrieve($orderId);
    }

    public function retrievePayMethods(string $userId, string $userEmail, ?string $configKey = null): RefundCreatedResponse
    {
        throw new \LogicException('Not implemented');
    }

    private function getConfig(?string $configKey = null): Configuration
    {
        if (null === $this->defaultConfigKey && null === $configKey) {
            throw new \LogicException('Config key must be provided.');
        }

        return $this->configurations[$configKey ?? $this->defaultConfigKey];
    }
}
