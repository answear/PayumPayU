<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\Util\JsonHelper;
use Answear\Payum\PayU\ValueObject\Response\PayMethodsResponse;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class PayMethods
{
    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public static function retrieve(?string $lang = null): PayMethodsResponse
    {
        try {
            $result = \OpenPayU_Retrieve::payMethods($lang);
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());

            return PayMethodsResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }
    }
}
