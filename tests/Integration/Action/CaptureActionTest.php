<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\PayU\Action\CaptureAction;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Request\PayMethodsRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\OrderCreatedStatus;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\StatusCode;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Answear\Payum\PayU\ValueObject\Response\PayMethodsResponse;
use Payum\Core\Gateway;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CaptureActionTest extends TestCase
{
    /**
     * @test
     */
    public function captureTest(): void
    {
        $captureAction = $this->getCaptureAction(
            expectedCreateRequest: FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/expectedOrderRequest.json')
        );

        $captureToken = new Token();
        $capture = new Capture($captureToken);
        $capture->setModel(new \Answear\Payum\PayU\Tests\Payment());
        $capture->setModel(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));

        $redirected = false;
        try {
            $captureAction->execute($capture);
        } catch (HttpRedirect $httpRedirect) {
            $redirected = true;
            self::assertSame('http://redirect-after-create-payment.url', $httpRedirect->getUrl());
        }

        self::assertTrue($redirected);
    }

    /**
     * @test
     */
    public function captureWithPayMethodTest(): void
    {
        $captureAction = $this->getCaptureAction(
            expectedCreateRequest: FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/expectedOrderRequestWithPayMethod.json')
        );

        $payment = new \Answear\Payum\PayU\Tests\Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));

        $payMethod = new PayMethod(
            PayMethodType::Pbl,
            'some value',
            'some authorisation code'
        );

        $captureToken = new Token();
        $capture = new \Answear\Payum\Action\Request\Capture($captureToken, $payment, $payMethod);

        $redirected = false;
        try {
            $captureAction->execute($capture);
        } catch (HttpRedirect $httpRedirect) {
            $redirected = true;
            self::assertSame('http://redirect-after-create-payment.url', $httpRedirect->getUrl());
        }

        self::assertTrue($redirected);
    }

    /**
     * @test
     */
    public function captureWithFailResponseTest(): void
    {
        $captureAction = $this->getCaptureAction(
            new OrderCreatedResponse(
                new OrderCreatedStatus(
                    StatusCode::ErrorValueMissing,
                    '	Brakuje jednej lub więcej wartości.',
                    'MISSING_REFUND_SECTION'
                ),
                '',
                '',
                'vjis3d90tsozmuj0rjgs3i'
            )
        );

        $payment = $this->createMock(Payment::class);
        $captureToken = new Token();
        $capture = new Capture($captureToken);
        $capture->setModel($payment);
        $capture->setModel(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));

        $withException = false;
        try {
            $captureAction->execute($capture);
        } catch (PayUException $exception) {
            $withException = true;
            self::assertSame('Create payment fails.', $exception->getMessage());
            self::assertSame(
                [
                    'status' => [
                        'statusCode' => 'ERROR_VALUE_MISSING',
                        'statusDesc' => '	Brakuje jednej lub więcej wartości.',
                        'codeLiteral' => 'MISSING_REFUND_SECTION',
                    ],
                    'redirectUri' => '',
                    'orderId' => '',
                    'extOrderId' => 'vjis3d90tsozmuj0rjgs3i',
                    'payMethods' => null,
                ],
                $exception->response
            );
            self::assertEquals(
                new Model(
                    [
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
                        'payuResponse' => [
                            'status' => [
                                'statusCode' => 'ERROR_VALUE_MISSING',
                                'statusDesc' => '	Brakuje jednej lub więcej wartości.',
                                'codeLiteral' => 'MISSING_REFUND_SECTION',
                            ],
                            'redirectUri' => '',
                            'orderId' => '',
                            'extOrderId' => 'vjis3d90tsozmuj0rjgs3i',
                            'payMethods' => null,
                        ],
                    ]
                ),
                $exception->model
            );
            self::assertSame($payment, $exception->payment);
        }

        self::assertTrue($withException);
    }

    /**
     * @test
     */
    public function captureWithOrderIdFailsTest(): void
    {
        $captureAction = $this->getCaptureAction(null, FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));

        $captureToken = new Token();
        $capture = new Capture($captureToken);
        $capture->setModel(new \Answear\Payum\PayU\Tests\Payment());
        $capture->setModel(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Capture payment with order id present is forbidden.');
        $captureAction->execute($capture);
    }

    /**
     * @test
     */
    public function captureWithCardOnFile(): void
    {
        $payMethodRequestService = $this->createMock(PayMethodsRequestService::class);
        $payMethodRequestService->method('retrieveForUser')
            ->willReturn(
                PayMethodsResponse::fromResponse(
                    FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/retrievePayMethodsForUserResponse.json')
                )
            );

        $captureAction = $this->getCaptureAction(
            expectedCreateRequest: FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/expectedOrderRequestWithCardOnFile.json'),
            details: FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithCardOnFileStandardMerchant.json'),
            payMethodsRequestService: $payMethodRequestService,
        );

        $payment = new \Answear\Payum\PayU\Tests\Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithCardOnFileStandardMerchant.json'));

        $payMethod = new PayMethod(
            PayMethodType::Pbl,
            'some value',
            'some authorisation code'
        );

        $captureToken = new Token();
        $capture = new \Answear\Payum\Action\Request\Capture($captureToken, $payment, $payMethod);

        $redirected = false;
        try {
            $captureAction->execute($capture);
        } catch (HttpRedirect $httpRedirect) {
            $redirected = true;
            self::assertSame('http://redirect-after-create-payment.url', $httpRedirect->getUrl());
        }

        self::assertTrue($redirected);
    }

    private function getCaptureAction(
        ?OrderCreatedResponse $response = null,
        ?array $details = null,
        ?array $expectedCreateRequest = null,
        ?MockObject $payMethodsRequestService = null,
    ): CaptureAction {
        $response = $response ?? new OrderCreatedResponse(
            new OrderCreatedStatus(
                StatusCode::Success,
                'Żądanie zostało wykonane poprawnie.'
            ),
            'http://redirect-after-create-payment.url',
            'WZHF5FFDRJ140731GUEST000P01',
            'vjis3d90tsozmuj0rjgs3i'
        );

        $orderRequestService = $this->createMock(OrderRequestService::class);
        $orderRequestService->method('create')
            ->with(
                $this->callback(
                    static function (OrderRequest $createRequest) use ($expectedCreateRequest) {

                        if ($expectedCreateRequest) {
                            $jsonE = json_encode($createRequest->toArray('posId'));
                            self::assertSame($createRequest->toArray('posId'), $expectedCreateRequest);
                        }

                        return true;
                    }
                )
            )
            ->willReturn($response);

        $captureAction = new CaptureAction(
            $orderRequestService,
            $payMethodsRequestService ?? $this->createMock(PayMethodsRequestService::class)
        );

        $notifyToken = $this->createMock(TokenInterface::class);
        $notifyToken->method('getTargetUrl')
            ->willReturn('http://notify.url');

        $tokenFactory = $this->createMock(GenericTokenFactory::class);
        $tokenFactory->method('createNotifyToken')
            ->willReturn($notifyToken);
        $captureAction->setGenericTokenFactory($tokenFactory);

        $gateway = $this->createMock(Gateway::class);
        $gateway->method('execute')
            ->with(
                $this->callback(
                    static function ($request) use ($details) {
                        if ($request instanceof GetHumanStatus) {
                            // unknown to skip convert action
                            $request->markUnknown();

                            $payment = $request->getFirstModel();
                            if ($payment instanceof MockObject) {
                                $payment->method('getDetails')
                                    ->willReturn($details ?? FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));
                            }
                            if ($payment instanceof PaymentInterface) {
                                $payment->setDetails($details ?? FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));
                            }
                        }

                        if ($request instanceof Convert) {
                            $request->setResult($details ?? FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/details.json'));
                        }

                        return true;
                    }
                )
            );
        $captureAction->setGateway($gateway);

        return $captureAction;
    }
}
