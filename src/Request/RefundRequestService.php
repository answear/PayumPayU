<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Client\Client;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\Refund as RefundResponse;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class RefundRequestService
{
    private const ENDPOINT = 'orders/';

    public function __construct(
        private Client $client,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function create(string $orderId, RefundRequest $refundRequest, ?string $configKey): RefundCreatedResponse
    {
        Assert::notEmpty($orderId);

        try {
            $refundRequestData = $refundRequest->toArray();
            $this->logger->info(
                '[Request] Create refund',
                [
                    'orderId' => $orderId,
                    'request' => $refundRequestData,
                ]
            );

            $result = $this->client->payuRequest(
                RefundRequest::METHOD,
                self::ENDPOINT . $orderId . '/refund',
                $this->client->getAuthorizeHeaders(
                    RefundRequest::AUTH_TYPE,
                    $configKey
                ),
                array_merge(['orderId' => $orderId], $refundRequestData)
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info(
                '[Response] Create refund',
                [
                    'orderId' => $orderId,
                    'response' => $response,
                ]
            );

            $refundCreatedResponse = RefundCreatedResponse::fromResponse($response);
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }

        if (ResponseStatusCode::Success !== $refundCreatedResponse->status->statusCode) {
            $payURequestException = new PayURequestException('Refund failed.');
            $payURequestException->response = $response;
            throw $payURequestException;
        }

        return $refundCreatedResponse;
    }

    /**
     * @return array<RefundResponse>
     *
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveRefundList(string $orderId, ?string $configKey): array
    {
        Assert::notEmpty($orderId);

        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . $orderId . '/refunds',
                $this->client->getAuthorizeHeaders(
                    RefundRequest::AUTH_TYPE,
                    $configKey
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (!\is_array($response['refunds'])) {
                $response['refunds'] = [];
            }

            $this->logger->info(
                '[Response] Refunds list',
                [
                    'orderId' => $orderId,
                    'response' => $response,
                ]
            );

            return array_map(
                static fn(array $refund) => RefundResponse::fromResponse($refund),
                $response['refunds']
            );
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveSingleRefund(string $orderId, string $refundId, ?string $configKey): RefundResponse
    {
        Assert::notEmpty($orderId);
        Assert::notEmpty($refundId);

        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . $orderId . '/refunds/' . $refundId,
                $this->client->getAuthorizeHeaders(
                    RefundRequest::AUTH_TYPE,
                    $configKey
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return RefundResponse::fromResponse($response);
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }
    }
}
