<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Authorization\Authorize;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response;
use Payum\Core\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class Api
{
    private readonly ?string $defaultConfigKey;
    private Request\Shop $shopRequestService;
    private Request\Order $orderRequestService;
    private Request\PayMethods $payMethodsRequestService;
    private Request\Refund $refundRequestService;

    /**
     * @param array<string, Configuration> $configurations
     */
    public function __construct(
        protected array $configurations,
        private readonly LoggerInterface $logger
    ) {
        try {
            Assert::allIsInstanceOf($configurations, Configuration::class);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        $this->defaultConfigKey = (1 === \count($configurations)) ? array_key_first($configurations) : null;
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function shopInfo(?string $configKey = null): Response\ShopInfo
    {
        $config = $this->getConfig($configKey);
        Authorize::withClientSecret($config);

        return $this->getShopRequest()->getShopInfo($config->publicShopId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function createOrder(OrderRequest $orderRequest, ?string $configKey = null): Response\OrderCreatedResponse
    {
        $config = $this->getConfig($configKey);
        Authorize::base($config);

        return $this->getOrderRequest()->create($orderRequest, $config->posId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function createRefund(string $orderId, RefundRequest $refundRequest, ?string $configKey = null): Response\RefundCreatedResponse
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getRefundRequest()->create($orderId, $refundRequest);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrieveOrder(string $orderId, ?string $configKey = null): Response\OrderRetrieveResponse
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getOrderRequest()->retrieve($orderId);
    }

    /**
     * @return array<Response\OrderTransactions\OrderRetrieveTransactionsResponseInterface>
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
     * @return array<Response\Refund>
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
    public function retrieveSingleRefund(string $orderId, string $refundId, ?string $configKey = null): Response\Refund
    {
        Authorize::base($this->getConfig($configKey));

        return $this->getRefundRequest()->retrieveSingleRefund($orderId, $refundId);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethods(?string $lang = null, ?string $configKey = null): Response\PayMethodsResponse
    {
        Authorize::withClientSecret($this->getConfig($configKey));

        return $this->getPayMethodsRequest()->retrieve($lang);
    }

    /**
     * @throws Exception\MalformedResponseException
     * @throws Exception\PayUException
     */
    public function retrievePayMethodsForUser(
        string $userId,
        string $userEmail,
        ?string $lang = null,
        ?string $configKey = null
    ): Response\PayMethodsResponse {
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

        if (!isset($this->configurations[$configKey ?? $this->defaultConfigKey])) {
            throw new \RuntimeException(sprintf('Invalid config key %s provided.', $configKey ?? $this->defaultConfigKey));
        }

        return $this->configurations[$configKey ?? $this->defaultConfigKey];
    }

    private function getShopRequest(): Request\Shop
    {
        if (!isset($this->shopRequestService)) {
            $this->shopRequestService = new Request\Shop();
        }

        return $this->shopRequestService;
    }

    private function getOrderRequest(): Request\Order
    {
        if (!isset($this->orderRequestService)) {
            $this->orderRequestService = new Request\Order($this->logger);
        }

        return $this->orderRequestService;
    }

    private function getPayMethodsRequest(): Request\PayMethods
    {
        if (!isset($this->payMethodsRequestService)) {
            $this->payMethodsRequestService = new Request\PayMethods($this->logger);
        }

        return $this->payMethodsRequestService;
    }

    private function getRefundRequest(): Request\Refund
    {
        if (!isset($this->refundRequestService)) {
            $this->refundRequestService = new Request\Refund($this->logger);
        }

        return $this->refundRequestService;
    }
}
