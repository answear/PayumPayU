<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Util\JsonHelper;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByCreditCard;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByPBL;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\OrderRetrieveTransactionsResponseInterface;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class Order
{
    private const CREDIT_CARD_VALUE = 'c';

    /**
     * @throws MalformedResponseException
     * @throws \OpenPayU_Exception
     */
    public static function create(OrderRequest $orderRequest): OrderCreatedResponse
    {
        $result = \OpenPayU_Order::create($orderRequest->toArray());

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return OrderCreatedResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }

    /**
     * @throws MalformedResponseException
     * @throws \OpenPayU_Exception
     */
    public static function retrieve(string $orderId): OrderRetrieveResponse
    {
        $result = \OpenPayU_Order::retrieve($orderId);

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return OrderRetrieveResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }

    /**
     * @return array<OrderRetrieveTransactionsResponseInterface>
     *
     * @throws \OpenPayU_Exception
     * @throws MalformedResponseException
     */
    public static function retrieveTransactions(string $orderId): array
    {
        $result = \OpenPayU_Order::retrieveTransaction($orderId);

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            $transactions = [];
            foreach ($response['transactions'] as $responseTransaction) {
                if (self::CREDIT_CARD_VALUE === $responseTransaction['payMethod']['value']) {
                    $transactions[] = ByCreditCard::fromResponse($responseTransaction);
                } else {
                    $transactions[] = ByPBL::fromResponse($responseTransaction);
                }
            }

            return $transactions;
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }
}
