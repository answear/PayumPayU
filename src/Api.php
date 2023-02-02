<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Authorization\Authorize;
use Answear\Payum\PayU\Request\Order;
use Answear\Payum\PayU\Request\PayMethods;
use Answear\Payum\PayU\Request\Refund;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\OrderRetrieveTransactionsResponseInterface;
use Answear\Payum\PayU\ValueObject\Response\PayMethodsResponse;
use Answear\Payum\PayU\ValueObject\Response\Refund as RefundResponse;
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
     * @throws Exception\PayUException
     */
    public function createOrder(OrderRequest $orderRequest, ?string $configKey = null): OrderCreatedResponse
    {
        $config = $this->getConfig($configKey);
        Authorize::base($config);

        return Order::create($orderRequest, $config->posId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function createRefund(string $orderId, RefundRequest $refundRequest, ?string $configKey = null): RefundCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Refund::create($orderId, $refundRequest);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveOrder(string $orderId, ?string $configKey = null): OrderRetrieveResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Order::retrieve($orderId);
    }

    /**
     * @return array<OrderRetrieveTransactionsResponseInterface>
     *
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveTransactions(string $orderId, ?string $configKey = null): array
    {
        Authorize::base($this->getConfig($configKey));

        return Order::retrieveTransactions($orderId);
    }

    /**
     * @return array<RefundResponse>
     *
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveRefundList(string $orderId, ?string $configKey = null): array
    {
        Authorize::base($this->getConfig($configKey));

        return Refund::retrieveRefundList($orderId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveSingleRefund(string $orderId, string $refundId, ?string $configKey = null): RefundResponse
    {
        Authorize::base($this->getConfig($configKey));

        return Refund::retrieveSingleRefund($orderId, $refundId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethods(?string $lang = null, ?string $configKey = null): PayMethodsResponse
    {
        Authorize::withClientSecret($this->getConfig($configKey));

        return PayMethods::retrieve($lang);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethodsForUser(string $userId, string $userEmail, ?string $lang = null, ?string $configKey = null): PayMethodsResponse
    {
        Authorize::withTrusted($this->getConfig($configKey), $userId, $userEmail);

        return PayMethods::retrieve($lang);
    }

    public function signatureIsValid(string $signatureHeader, string $data, ?string $configKey = null): bool
    {
        $config = $this->getConfig($configKey);

        $signature = \OpenPayU_Util::parseSignature($signatureHeader);

        return \OpenPayU_Util::verifySignature(
            $data,
            $signature['signature'],
            $config->signatureKey,
            $signature['algorithm']
        );
    }

    private function getConfig(?string $configKey = null): Configuration
    {
        if (null === $this->defaultConfigKey && null === $configKey) {
            throw new \InvalidArgumentException('Config key must be provided.');
        }

        return $this->configurations[$configKey ?? $this->defaultConfigKey];
    }
}
