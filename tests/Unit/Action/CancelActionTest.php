<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit\Action;

use Answear\Payum\PayU\Action\CancelAction;
use Answear\Payum\PayU\Exception\CannotCancelPaymentException;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Payment;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\OrderCanceledResponse;
use Answear\Payum\PayU\ValueObject\Response\OrderRetrieveResponse;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\TestCase;

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
                    __DIR__ . '/../../Integration/Request/data/orderCanceledResponse.json'
                )
            ),
            OrderRetrieveResponse::fromResponse(
                FileTestUtil::decodeJsonFromFile(
                    __DIR__ . '/../../Integration/Request/data/retrieveOrderResponse.json'
                )
            )
        );

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/../../Integration/Action/data/detailsWithOrderId.json'));

        $request = new Cancel($payment);
        $request->setModel($payment->getDetails());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function orderHasFinalStatusTest(): void
    {
        $this->expectException(CannotCancelPaymentException::class);
        $this->expectExceptionMessage('Order status is final, cannot cancel payment.');

        $action = $this->getCancelAction(
            null,
            OrderRetrieveResponse::fromResponse(
                FileTestUtil::decodeJsonFromFile(
                    __DIR__ . '/../../Integration/Request/data/retrieveOrderWithPayMethodResponse.json'
                )
            )
        );

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/../../Integration/Action/data/detailsWithOrderId.json'));

        $request = new Cancel($payment);
        $request->setModel($payment->getDetails());

        $action->execute($request);
    }

    private function getCancelAction(
        ?OrderCanceledResponse $orderCanceledResponse,
        OrderRetrieveResponse $retrieveOrderResponse
    ): CancelAction {
        $orderRequestService = $this->createMock(OrderRequestService::class);
        $orderRequestService->expects(self::once())
            ->method('retrieve')
            ->willReturn($retrieveOrderResponse);

        if (null === $orderCanceledResponse) {
            $orderRequestService->expects(self::never())
                ->method('cancel');
        } else {
            $orderRequestService->expects(self::once())
                ->method('cancel')
                ->willReturn($orderCanceledResponse);
        }

        return new CancelAction($orderRequestService);
    }
}
