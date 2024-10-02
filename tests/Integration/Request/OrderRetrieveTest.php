<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayUNetworkException;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\PayMethod;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Response\Order;
use Answear\Payum\PayU\ValueObject\Response\Property;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;

class OrderRetrieveTest extends AbstractRequestTestCase
{
    #[Test]
    public function retrieveTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveOrderResponse.json'))
        );

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getOrderRequestService()->retrieve($orderId, null);
        self::assertEquals(
            new ResponseStatus(
                ResponseStatusCode::Success,
                'Request processing successful'
            ),
            $response->status
        );
        self::assertCount(1, $response->orders);
        self::assertEquals(
            new Order(
                'WZHF5FFDRJ140731GUEST000P01',
                '358766',
                new \DateTimeImmutable('2014-10-27T14:58:17.443+01:00'),
                'http://localhost/OrderNotify/',
                '127.0.0.1',
                '145227',
                'New order',
                null,
                'PLN',
                3200,
                [
                    new Product(
                        'Product1',
                        1000,
                        1,
                    ),
                    new Product(
                        'Product2',
                        2200,
                        1,
                    ),
                ],
                OrderStatus::New,
                new Buyer(
                    'john.doe@example.org',
                    'John',
                    'Doe',
                    '111111111',
                    language: 'pl'
                ),
            ),
            $response->orders[0]
        );
        self::assertEquals(
            [
                new Property('PAYMENT_ID', '1234567890'),
            ],
            $response->properties
        );
    }

    #[Test]
    public function retrieveWithPayMethodTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveOrderWithPayMethodResponse.json'))
        );

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getOrderRequestService()->retrieve($orderId, null);
        self::assertEquals(
            new ResponseStatus(
                ResponseStatusCode::Success,
                'Request processing successful'
            ),
            $response->status
        );
        self::assertCount(1, $response->orders);
        self::assertEquals(
            new Order(
                'S2GZ99XFWL220125GUEST000P01',
                '220125-0101N1-PL',
                new \DateTimeImmutable('2022-01-25T12:20:39.639+01:00'),
                'http://gogo.go/payment/notify/2hG7ENuL3HpvB',
                '10.0.10.219',
                '345503',
                'Zapłata za zamówienie nr: 220125-0101N1-PL',
                null,
                'PLN',
                17346,
                [
                    new Product(
                        'Zapłata za zamówienie nr: 220125-0101N1-PL',
                        17346,
                        1,
                    ),
                ],
                OrderStatus::Completed,
                new Buyer(
                    'test@test-fake.pl',
                    'bat',
                    'batowisz',
                    '+48577777777',
                    'guest',
                    language: 'pl'
                ),
                new PayMethod(PayMethodType::CardToken),
                259200
            ),
            $response->orders[0]
        );
        self::assertEquals(
            [
                new Property('PAYMENT_ID', '5003299991'),
            ],
            $response->properties
        );
    }

    #[Test]
    public function notFoundTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(404, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveNoOrderResponse.json'))
        );

        $this->expectException(PayUNetworkException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageMatches('/DATA_NOT_FOUND/');

        $this->getOrderRequestService()->retrieve('WZHF5FFDRJ140731GUEST000P01', null);
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
