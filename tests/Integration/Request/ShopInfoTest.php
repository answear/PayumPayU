<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Tests\Util\FileTestUtil;

class ShopInfoTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function shopInfoTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/shopInfoResponse.json'));

        $response = $this->getApiService()->shopInfo();
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
}
