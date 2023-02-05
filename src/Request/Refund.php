<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Service\PayURefundService;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\Util\JsonHelper;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\Refund as RefundResponse;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use Psr\Log\LoggerInterface;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class Refund
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function create(string $orderId, RefundRequest $refundRequest): RefundCreatedResponse
    {
        try {
            $this->logger->info(
                '[Request] Create refund',
                [
                    'orderId' => $orderId,
                    'request' => $refundRequest->toArray(),
                ]
            );

            $result = \OpenPayU_Refund::create(
                $orderId,
                $refundRequest->refund->description,
                $refundRequest->refund->amount,
                $refundRequest->refund->extCustomerId,
                $refundRequest->refund->extRefundId
            );
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());
            $this->logger->info(
                '[Response] Create refund',
                [
                    'orderId' => $orderId,
                    'response' => $response,
                ]
            );

            return RefundCreatedResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }

    /**
     * @return array<RefundResponse>
     *
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveRefundList(string $orderId): array
    {
        try {
            $result = PayURefundService::retrieveRefundList($orderId);
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return array_map(
                static fn(array $refund) => RefundResponse::fromResponse($refund),
                $response['refunds']
            );
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveSingleRefund(string $orderId, string $refundId): RefundResponse
    {
        try {
            $result = PayURefundService::retrieveSingleRefund($orderId, $refundId);
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return RefundResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }
}
