<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Client\Client;
use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\ValueObject\Response;

class ShopRequestService
{
    private const ENDPOINT = 'shops/';

    public function __construct(
        private ConfigProvider $configProvider,
        private Client $client
    ) {
    }

    /**
     * @throws PayUException
     */
    public function getShopInfo(?string $configKey): Response\ShopInfo
    {
        $config = $this->configProvider->getConfig($configKey);

        try {
            $result = $this->client->payuRequest(
                'GET',
                self::ENDPOINT . $config->publicShopId,
                $this->client->getAuthorizeHeaders(
                    AuthType::OAuthClientCredentials,
                    $configKey
                )
            );
            $response = json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return Response\ShopInfo::fromResponse($response);
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }
    }
}
