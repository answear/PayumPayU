<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Authorization\Authorize;
use Answear\Payum\PayU\Request\CreateOrder;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
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

    public function createOrder(OrderRequest $orderRequest, ?string $configKey = null): OrderCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return CreateOrder::create($orderRequest);
    }

    public function createRefund(RefundRequest $refundRequest, ?string $configKey = null): RefundCreatedResponse
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * @see OrderCreatedResponse::$orderId
     */
    public function retrieveOrder(string $orderId): mixed
    {
        throw new \LogicException('Not implemented');
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
