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
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class Api
{
    private ?string $defaultConfigKey = null;
    private Order $orderRequestService;
    private PayMethods $payMethodsRequestService;
    private Refund $refundRequestService;

    /**
     * @param array<string, Configuration> $configurations
     */
    public function __construct(
        protected array $configurations,
        private LoggerInterface $logger
    ) {
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

        return $this->getOrderRequest()->create($orderRequest, $config->posId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function createRefund(string $orderId, RefundRequest $refundRequest, ?string $configKey = null): RefundCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getRefundRequest()->create($orderId, $refundRequest);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveOrder(string $orderId, ?string $configKey = null): OrderRetrieveResponse
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getOrderRequest()->retrieve($orderId);
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

        return $this->getOrderRequest()->retrieveTransactions($orderId);
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

        return $this->getRefundRequest()->retrieveRefundList($orderId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveSingleRefund(string $orderId, string $refundId, ?string $configKey = null): RefundResponse
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getRefundRequest()->retrieveSingleRefund($orderId, $refundId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethods(?string $lang = null, ?string $configKey = null): PayMethodsResponse
    {
        Authorize::withClientSecret($this->getConfig($configKey));

        return $this->getPayMethodsRequest()->retrieve($lang);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethodsForUser(string $userId, string $userEmail, ?string $lang = null, ?string $configKey = null): PayMethodsResponse
    {
        Authorize::withTrusted($this->getConfig($configKey), $userId, $userEmail);

        return $this->getPayMethodsRequest()->retrieve($lang);
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

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function getConfig(?string $configKey = null): Configuration
    {
        if (null === $this->defaultConfigKey && null === $configKey) {
            throw new \InvalidArgumentException('Config key must be provided.');
        }

        return $this->configurations[$configKey ?? $this->defaultConfigKey];
    }

    private function getOrderRequest(): Order
    {
        if (!isset($this->orderRequestService)) {
            $this->orderRequestService = new Order($this->logger);
        }

        return $this->orderRequestService;
    }

    private function getPayMethodsRequest(): PayMethods
    {
        if (!isset($this->payMethodsRequestService)) {
            $this->payMethodsRequestService = new PayMethods();
        }

        return $this->payMethodsRequestService;
    }

    private function getRefundRequest(): Refund
    {
        if (!isset($this->refundRequestService)) {
            $this->refundRequestService = new Refund($this->logger);
        }

        return $this->refundRequestService;
    }
}
