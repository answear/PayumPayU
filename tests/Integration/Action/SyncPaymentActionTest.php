<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\Action\Request\SyncPayment;
use Answear\Payum\PayU\Action\SyncPaymentAction;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Payment;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SyncPaymentActionTest extends TestCase
{
    #[Test]
    public function successTest(): void
    {
        $action = $this->getUpdatePaymentAction(
            OrderRetrieveResponse::fromResponse(
                FileTestUtil::decodeJsonFromFile(
                    __DIR__ . '/../Request/data/retrieveOrderWithPayMethodResponse.json'
                )
            )
        );

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));
        $expectedBaseDetails = $this->getExpectedBaseDetails();
        self::assertSame($expectedBaseDetails, $payment->getDetails());

        $request = new SyncPayment($payment);
        $action->execute($request);

        self::assertSame(
            array_merge(
                $expectedBaseDetails,
                [
                    'status' => 'COMPLETED',
                    'properties' => [
                        'PAYMENT_ID' => '5003299991',
                    ],
                ]
            ),
            $payment->getDetails()
        );
    }

    private function getUpdatePaymentAction(OrderRetrieveResponse $orderRetrieveResponse): SyncPaymentAction
    {
        $orderRequestService = $this->createMock(OrderRequestService::class);
        $orderRequestService->method('retrieve')
            ->willReturn($orderRetrieveResponse);

        return new SyncPaymentAction($orderRequestService);
    }

    private function getExpectedBaseDetails(): array
    {
        return [
            'totalAmount' => 95500,
            'firstName' => 'Testy',
            'lastName' => 'Mjzykdwmh',
            'description' => 'Platnost za objednávku č.: 221214-0026UJ-CZ',
            'currencyCode' => 'CZK',
            'language' => 'cs',
            'validityTime' => 259200,
            'buyer' => [
                'email' => 'test@email-fake.domain',
                'firstName' => 'Testy',
                'lastName' => 'Mjzykdwmh',
                'phone' => '+420733999019',
                'language' => 'cs',
            ],
            'extOrderId' => '221214-0026UJ-CZ',
            'client_email' => 'test@email-fake.domain',
            'client_id' => '124077',
            'customerIp' => '10.0.13.152',
            'creditCardMaskedNumber' => null,
            'status' => 'PENDING',
            'payUResponse' => [
                'status' => [
                    'statusCode' => 'SUCCESS',
                ],
                'redirectUri' => 'https://merch-prod.snd.payu.com/pay/?orderId=3MRW8ST2Z6221214GUEST000P01&token=eyJhbGciOiJIUzI1NiJ9.eyJvcmRlcklkIjoiM01SVzhTVDJaNjIyMTIxNEdVRVNUMDAwUDAxIiwicG9zSWQiOiI5eWJZVWFZOSIsImF1dGhvcml0aWVzIjpbIlJPTEVfQ0xJRU5UIl0sInBheWVyRW1haWwiOiJ0ZXN0eS5hdXRvbWF0eWN6bmUrZGVjMTQyMDIyMjMyODUzNDgwMDIzQGFuc3dlYXIuY29tIiwiZXhwIjoxNjcxMzE2Mjk1LCJpc3MiOiJQQVlVIiwiYXVkIjoiYXBpLWdhdGV3YXkiLCJzdWIiOiJQYXlVIHN1YmplY3QiLCJqdGkiOiI3NmYyOGZkMi1jOWIxLTRiYzAtOTM5Zi0xNjQ5NjY0ZWNlZDMifQ.NpmBZw0vQP7WEWQEd-ZhXoyg8oo_eKy8gEyfAjri21g',
                'orderId' => '3MRW8ST2Z6221214GUEST000P01',
                'extOrderId' => '221214-0026UJ-CZ',
            ],
            'orderId' => '3MRW8ST2Z6221214GUEST000P01',
        ];
    }
}
