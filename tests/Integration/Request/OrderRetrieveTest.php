<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\PayMethod;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Response\Order;
use Answear\Payum\PayU\ValueObject\Response\Property;
use Answear\Payum\PayU\ValueObject\Response\RefundCreated\StatusCode;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use PHPUnit\Framework\TestCase;

class OrderRetrieveTest extends TestCase
{
    /**
     * @test
     */
    public function retrieveTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/retrieveOrderResponse.json'));

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getApiService()->retrieveOrder($orderId);
        self::assertEquals(
            new ResponseStatus(
                StatusCode::Success,
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

    /**
     * @test
     */
    public function retrieveWithPayMethodTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/retrieveOrderWithPayMethodResponse.json'));

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getApiService()->retrieveOrder($orderId);
        self::assertEquals(
            new ResponseStatus(
                StatusCode::Success,
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

    /**
     * @test
     */
    public function notFoundTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(404, FileTestUtil::getFileContents(__DIR__ . '/data/retrieveNoOrderResponse.json'));

        $this->expectException(\OpenPayU_Exception_Network::class);
        $this->expectExceptionMessage('DATA_NOT_FOUND - Could not find data for given criteria.');
        $this->getApiService()->retrieveOrder('WZHF5FFDRJ140731GUEST000P01');
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
