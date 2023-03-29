<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\Action\Request\Refund;
use Answear\Payum\PayU\Action\RefundAction;
use Answear\Payum\PayU\Enum\ModelFields;
use Answear\Payum\PayU\Request\RefundRequestService;
use Answear\Payum\PayU\Tests\Payment;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use PHPUnit\Framework\TestCase;

class RefundActionTest extends TestCase
{
    /**
     * @test
     */
    public function successTest(): void
    {
        $refundCreatedResponse = RefundCreatedResponse::fromResponse(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/refundCreatedResponse.json'));

        $refundAction = $this->getRefundAction($refundCreatedResponse);

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));
        $refund = new Refund($payment, 'Refund for xyz', 3029);
        $refund->setModel($payment->getDetails());
        self::assertNull($refund->refundCreatedResponse);

        self::assertNull($payment->getDetails()[ModelFields::REFUND] ?? null);

        $refundAction->execute($refund);

        self::assertSame($refundCreatedResponse, $refund->refundCreatedResponse);
        self::assertSame(
            [
                '5000009987' => [
                    'refundId' => '5000009987',
                    'extRefundId' => '20200702091903',
                    'amount' => 21000,
                    'currencyCode' => 'PLN',
                    'description' => 'Uznanie 5000009987 Refund',
                    'creationDateTime' => '2020-07-02T09:19:03+02:00',
                    'statusDateTime' => '2020-07-02T09:19:04+02:00',
                    'status' => 'PENDING',
                    'statusError' => null,
                ],
            ],
            $refund->getModel()[ModelFields::REFUND] ?? null
        );
    }

    /**
     * @test
     */
    public function errorTest(): void
    {
        $refundCreatedResponse = RefundCreatedResponse::fromResponse(
            FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/refundCreatedFailsResponse.json')
        );

        $refundAction = $this->getRefundAction($refundCreatedResponse);

        $payment = new Payment();
        $payment->setDetails(FileTestUtil::decodeJsonFromFile(__DIR__ . '/data/detailsWithOrderId.json'));
        $refund = new Refund($payment, 'Refund for xyz', 3029);
        $refund->setModel($payment->getDetails());
        self::assertNull($refund->refundCreatedResponse);

        self::assertNull($payment->getDetails()[ModelFields::REFUND] ?? null);

        $refundAction->execute($refund);

        self::assertSame($refundCreatedResponse, $refund->refundCreatedResponse);
        self::assertSame(
            [
                '5000009987' => [
                    'refundId' => '5000009987',
                    'extRefundId' => '20200702091903',
                    'amount' => 21000,
                    'currencyCode' => 'PLN',
                    'description' => 'Uznanie 5000009987 Refund',
                    'creationDateTime' => '2020-07-02T09:19:03+02:00',
                    'statusDateTime' => '2020-07-02T09:19:04+02:00',
                    'status' => 'PENDING',
                    'statusError' => null,
                ],
            ],
            $refund->getModel()[ModelFields::REFUND] ?? null
        );
    }

    private function getRefundAction(RefundCreatedResponse $refundCreatedResponse): RefundAction
    {
        $refundRequestService = $this->createMock(RefundRequestService::class);
        $refundRequestService->method('create')
            ->willReturn($refundCreatedResponse);

        return new RefundAction($refundRequestService);
    }
}
