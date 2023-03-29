<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Request\ShopRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use GuzzleHttp\Psr7\Response;

class ShopInfoTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function shopInfoTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/authorisationResponse.json'))
        );
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/shopInfoResponse.json'))
        );

        $response = $this->getShopRequestService()->getShopInfo(null);
        self::assertSame(
            [
                'Shop Checkout',
                'QFw0KGSX',
                'PLN',
                [
                    'PLN',
                    22039,
                    220839,
                ],
            ],
            [
                $response->name,
                $response->shopId,
                $response->currencyCode,
                [
                    $response->balance->currencyCode,
                    $response->balance->available,
                    $response->balance->total,
                ],
            ]
        );
    }

    private function getShopRequestService(): ShopRequestService
    {
        return new ShopRequestService(
            $this->getConfigProvider(),
            $this->getClient()
        );
    }
}
