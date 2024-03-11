<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\PayU\Action\CancelAction;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Payment;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\OrderCanceledResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CancelActionTest extends TestCase
{
    /**
     * @test
     */
    public function successTest(): void
    {
        $action = $this->getCancelAction(
            OrderCanceledResponse::fromResponse(
                FileTestUtil::decodeJsonFromFile(
                    __DIR__ . '/../Request/data/orderCanceledResponse.json'
                )
            ),
            OrderRetrieveResponse::fromResponse(
                FileTestUtil::decodeJsonFromFile(
                    __DIR__ . '/../Request/data/retrieveOrderResponse.json'
                )
            )
        );

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));
        $expectedBaseDetails = $this->getExpectedBaseDetails();
        self::assertSame($expectedBaseDetails, $payment->getDetails());

        $request = new Cancel($payment);
        $request->setModel($payment->getDetails());

        $action->execute($request);
    }

    private function getCancelAction(
        OrderCanceledResponse $orderCanceledResponse,
        OrderRetrieveResponse $retrieveOrderResponse
    ): CancelAction {
        $orderRequestService = $this->createMock(OrderRequestService::class);
        $orderRequestService->method('retrieve')
            ->willReturn($retrieveOrderResponse);

        $orderRequestService->method('cancel')
            ->willReturn($orderCanceledResponse);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())
            ->method('critical');

        return new CancelAction($orderRequestService, $logger);
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
