<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Util\JsonHelper;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class Refund
{
    /**
     * @throws MalformedResponseException
     * @throws \OpenPayU_Exception
     */
    public static function create(string $orderId, RefundRequest $refundRequest): RefundCreatedResponse
    {
        $result = \OpenPayU_Refund::create(
            $orderId,
            $refundRequest->refund->description,
            $refundRequest->refund->amount,
            $refundRequest->refund->extCustomerId,
            $refundRequest->refund->extRefundId
        );

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return RefundCreatedResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }
}
