<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\PayU\Action\NotifyAction;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Service\PayULogger;
use Answear\Payum\PayU\Service\SignatureValidator;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\Order;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Answear\Payum\PayU\ValueObject\Response\Property;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use Payum\Core\Gateway;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotifyActionTest extends TestCase
{
    #[Test]
    public function orderNotifyTest(): void
    {
        $notifyAction = $this->getNotifyAction();

        $notifyToken = new Token();
        $notify = new Notify($notifyToken);
        $payment = new Payment();
        $notify->setModel($payment);
        $notify->setModel(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));

        $currentModel = $notify->getModel()->getArrayCopy();
        self::assertSame('3MRW8ST2Z6221214GUEST000P01', $currentModel['orderId']);
        self::assertSame('PENDING', $currentModel['status']);
        self::assertSame([], $currentModel['properties'] ?? []);

        $httpResponse = false;
        try {
            $notifyAction->execute($notify);
        } catch (HttpResponse $response) {
            $httpResponse = true;
            self::assertSame(200, $response->getStatusCode());
            self::assertSame('OK', $response->getContent());
        }

        self::assertTrue($httpResponse);

        $model = $notify->getModel()->getArrayCopy();
        self::assertSame('LDLW5N7MF4140324GUEST000P01', $model['orderId']);
        self::assertSame('COMPLETED', $model['status']);
        self::assertSame(['PAYMENT_ID' => '151471228'], $model['properties'] ?? []);
    }

    #[Test]
    public function refundNotifyTest(): void
    {
        $notifyAction = $this->getNotifyAction(
            FileTestUtil::getFileContents(__DIR__ . '/data/refundNotifyData.json')
        );

        $notifyToken = new Token();
        $notify = new Notify($notifyToken);
        $payment = new Payment();
        $notify->setModel($payment);
        $notify->setModel(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));

        $currentModel = $notify->getModel()->getArrayCopy();
        self::assertSame('3MRW8ST2Z6221214GUEST000P01', $currentModel['orderId']);
        self::assertSame('PENDING', $currentModel['status']);
        self::assertSame([], $currentModel['properties'] ?? []);
        try {
            $notifyAction->execute($notify);
        } catch (HttpResponse $response) {
            self::assertSame(200, $response->getStatusCode());
            self::assertSame('OK', $response->getContent());
        }

        $model = $notify->getModel()->getArrayCopy();
        self::assertSame('3MRW8ST2Z6221214GUEST000P01', $model['orderId']);
        self::assertSame(
            [
                '912128' => [
                    'refundId' => '912128',
                    'amount' => '15516',
                    'currencyCode' => 'PLN',
                    'status' => 'FINALIZED',
                    'statusDateTime' => '2014-08-20T19:43:31.418+02:00',
                    'reason' => 'refund',
                    'reasonDescription' => 'na życzenie klienta',
                    'refundDate' => '2014-08-20T19:43:30.150+02:00',
                ],
            ],
            $model['refund'] ?? []
        );
    }

    private function getNotifyAction(?string $content = null): NotifyAction
    {
        $orderRequestService = $this->createMock(OrderRequestService::class);
        $orderRequestService->method('retrieve')
            ->willReturn(
                new OrderRetrieveResponse(
                    [
                        Order::fromResponse(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/orderNotifyData.json')['order']),
                    ],
                    new ResponseStatus(ResponseStatusCode::Success),
                    [
                        new Property('PAYMENT_ID', '151471228'),
                    ]
                )
            );

        $signatureValidator = $this->createMock(SignatureValidator::class);
        $signatureValidator->method('isValid')
            ->willReturn(true);

        $notifyAction = new NotifyAction(
            $orderRequestService,
            $signatureValidator,
            new PayULogger(null)
        );

        $gateway = $this->createMock(Gateway::class);
        $gateway->method('execute')
            ->with(
                $this->callback(
                    static function ($request) use ($content) {
                        if ($request instanceof GetHttpRequest) {
                            $request->headers = ['openpayu-signature' => 'signatureHash'];
                            $request->content = $content ?? FileTestUtil::getFileContents(__DIR__ . '/data/orderNotifyData.json');
                        }

                        return true;
                    }
                )
            );

        $notifyAction->setGateway($gateway);

        return $notifyAction;
    }
}
