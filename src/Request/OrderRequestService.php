<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Client\Client;
use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByCreditCard;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByPBL;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\OrderRetrieveTransactionsResponseInterface;
use Psr\Log\LoggerInterface;

class OrderRequestService
{
    private const CREDIT_CARD_VALUE = 'c';
    private const ENDPOINT = 'orders/';
    private const ORDER_TRANSACTION_SERVICE = 'transactions';

    public function __construct(
        private ConfigProvider $configProvider,
        private Client $client,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function create(OrderRequest $orderRequest, ?string $configKey): OrderCreatedResponse
    {
        $config = $this->configProvider->getConfig($configKey);
        $merchantPosId = $config->posId;

        try {
            $orderRequestData = $orderRequest->toArray($merchantPosId);
            $this->logger->info(
                '[Request] Create order',
                [
                    'posId' => $merchantPosId,
                    'request' => $orderRequestData,
                ]
            );

            $result = $this->client->payuRequest(
                OrderRequest::METHOD,
                self::ENDPOINT,
                $this->client->getAuthorizeHeaders(
                    OrderRequest::AUTH_TYPE,
                    $configKey
                ),
                $orderRequestData
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info(
                '[Response] Create order',
                [
                    'posId' => $merchantPosId,
                    'response' => $response,
                ]
            );

            return OrderCreatedResponse::fromResponse($response);
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieve(string $orderId, ?string $configKey): OrderRetrieveResponse
    {
        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . $orderId,
                $this->client->getAuthorizeHeaders(
                    AuthType::Basic,
                    $configKey
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return OrderRetrieveResponse::fromResponse($response);
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }
    }

    /**
     * @return array<OrderRetrieveTransactionsResponseInterface>
     *
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveTransactions(string $orderId, ?string $configKey): array
    {
        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . $orderId . '/' . self::ORDER_TRANSACTION_SERVICE,
                $this->client->getAuthorizeHeaders(
                    AuthType::Basic,
                    $configKey
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info(
                '[Request] retrieveTransactions',
                [
                    'orderId' => $orderId,
                    'response' => $response,
                ]
            );

            if (false === $response['transactions']) {
                return [];
            }

            $transactions = [];
            foreach ($response['transactions'] as $responseTransaction) {
                if (self::CREDIT_CARD_VALUE === $responseTransaction['payMethod']['value']) {
                    $transactions[] = ByCreditCard::fromResponse($responseTransaction);
                } else {
                    $transactions[] = ByPBL::fromResponse($responseTransaction);
                }
            }

            return $transactions;
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }
    }
}
