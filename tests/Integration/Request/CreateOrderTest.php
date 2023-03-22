<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Exception\PayUAuthorizationException;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\OrderCreatedStatus;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\StatusCode;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;

class CreateOrderTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function createTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderCreatedResponse.json'))
        );

        $orderRequest = new OrderRequest(
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

        $orderCreated = $this->getOrderRequestService()->create($orderRequest, null);
        self::assertEquals($orderCreated->status, new OrderCreatedStatus(StatusCode::Success));
        self::assertSame('http://continue.url.com', $orderCreated->redirectUri);
        self::assertSame('WZHF5FFDRJ140731GUEST000P01', $orderCreated->orderId);
        self::assertSame('extOrderId123', $orderCreated->extOrderId);
    }

    /**
     * @test
     */
    public function createUnauthorizedTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(401, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderUnauthorizedResponse.json'))
        );

        $orderRequest = $this->createMock(OrderRequest::class);

        $this->expectException(PayUAuthorizationException::class);
        $this->expectExceptionCode(401);
        $this->getOrderRequestService()->create($orderRequest, null);
    }

    private function getOrderRequestService(): OrderRequestService
    {
        return new OrderRequestService(
            $this->getConfigProvider(),
            $this->getClient(),
            new NullLogger()
        );
    }
}
