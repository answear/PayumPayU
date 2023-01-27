<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\Status;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\StatusCode;
use PHPUnit\Framework\TestCase;

class CreateOrderTest extends TestCase
{
    /**
     * @test
     */
    public function createTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/orderCreatedResponse.json'));

        $orderRequest = new OrderRequest(
            'merchantPosId435',
            'description',
            'PL',
            267435,
            '127.0.0.1',
            'http://notify.url.fake',
            [
                new Product(
                    'Product name',
                    1265,
                    2
                ),
            ],
            152,
            'EXT-2836',
            'http://continue.url.com',
            new Buyer(
                'buyer@no-domain.com',
                'Firstname',
                'Surname',
                '+48209328762',
                '127.0.0.1'
            ),
            new PayMethod(PayMethodType::PaymentWall, 'c'),
            'Additional Description'
        );

        $orderCreated = $this->getApiService()->createOrder($orderRequest);
        self::assertEquals($orderCreated->status, new Status(StatusCode::Success));
        self::assertSame('http://continue.url.com', $orderCreated->redirectUri);
        self::assertSame('WZHF5FFDRJ140731GUEST000P01', $orderCreated->orderId);
        self::assertSame('extOrderId123', $orderCreated->extOrderId);
    }

    /**
     * @test
     */
    public function createUnauthorizedTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(401, FileTestUtil::getFileContents(__DIR__ . '/data/orderUnauthorizedResponse.json'));

        $orderRequest = $this->createMock(OrderRequest::class);

        $this->expectException(\OpenPayU_Exception_Authorization::class);
        $this->expectExceptionMessage('UNAUTHORIZED - Incorrect authentication');
        $this->getApiService()->createOrder($orderRequest);
    }

    private function getApiService(): Api
    {
        return new Api(
            [
                'one_pos' => new Configuration(
                    Environment::Sandbox,
                    '123',
                    's-key',
                    'cl-id',
                    'sec',
                ),
            ],
        );
    }
}
