<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\Util\JsonHelper;
use Answear\Payum\PayU\ValueObject\Response\PayMethodsResponse;
use Psr\Log\LoggerInterface;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class PayMethods
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieve(?string $lang = null): PayMethodsResponse
    {
        try {
            $result = \OpenPayU_Retrieve::payMethods($lang);
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }

        try {
            $response = JsonHelper::getArrayFromObject($result->getResponse());
            $this->logger->info(
                '[Request] PayMethods retrieve',
                [
                    'lang' => $lang,
                    'response' => $response,
                ]
            );

            $payMethodsResponse = PayMethodsResponse::fromResponse($response);
        } catch (\Throwable $e) {
            throw new MalformedResponseException($response ?? [], $e);
        }

        if (ResponseStatusCode::Success !== $payMethodsResponse->status->statusCode) {
            $payURequestException = new PayURequestException('Getting pay methods failed.');
            $payURequestException->response = $response;
            throw $payURequestException;
        }

        return $payMethodsResponse;
    }
}
