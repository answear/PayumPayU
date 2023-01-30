<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;

/**
 * @interal use Api class instead
 */
class CreateOrder
{
    public static function create(OrderRequest $orderRequest): OrderCreatedResponse
    {
        /** @see \OpenPayU_Http::throwHttpStatusException */
        $result = \OpenPayU_Order::create($orderRequest->toArray());

        try {
            $response = json_decode(
                json_encode($result->getResponse(), JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return OrderCreatedResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }
}
