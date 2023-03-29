<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Client\Client;
use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\MalformedResponseException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\ValueObject\Response\PayMethodsResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class PayMethodsRequestService
{
    private const ENDPOINT = 'paymethods';

    public function __construct(private Client $client, private LoggerInterface $logger)
    {
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieve(?string $configKey, ?string $lang = null): PayMethodsResponse
    {
        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . (null === $lang ? '' : '?lang=' . $lang),
                $this->client->getAuthorizeHeaders(
                    AuthType::OAuthClientCredentials,
                    $configKey
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        return $this->prepareResponse($result);
    }

    /**
     * @throws MalformedResponseException
     * @throws PayUException
     */
    public function retrieveForUser(string $email, string $extCustomerId, ?string $configKey, ?string $lang = null): PayMethodsResponse
    {
        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . (null === $lang ? '' : '?lang=' . $lang),
                $this->client->getAuthorizeHeaders(
                    AuthType::OAuthClientTrustedMerchant,
                    $configKey,
                    $email,
                    $extCustomerId
                )
            );
        } catch (\Throwable $throwable) {
            throw ExceptionHelper::getPayUException($throwable);
        }

        return $this->prepareResponse($result);
    }

    private function prepareResponse(ResponseInterface $result): PayMethodsResponse
    {
        try {
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info(
                '[Request] PayMethods retrieve',
                [
                    'response' => $response,
                ]
            );

            $payMethodsResponse = PayMethodsResponse::fromResponse($response);
        } catch (\Throwable $throwable) {
            throw new MalformedResponseException($response ?? [], $throwable);
        }

        if (ResponseStatusCode::Success !== $payMethodsResponse->status->statusCode) {
            $payURequestException = new PayURequestException('Getting pay methods failed.');
            $payURequestException->response = $response;
            throw $payURequestException;
        }

        return $payMethodsResponse;
    }
}
