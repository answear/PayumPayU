<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\RefundStatus;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Request\RefundRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Request\Refund\Refund;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\Refund as ResponseRefund;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;

class CreateRefundTest extends AbstractRequestTestCase
{
    #[Test]
    public function createTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/refundCreatedResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundRequest = new RefundRequest(
            new Refund(
                'Uznanie 5000009987 Refund',
                21000,
                extRefundId: '20200702091903'
            )
        );

        $refundCreated = $this->getRefundRequestService()->create($orderId, $refundRequest, null);
        self::assertEquals(new ResponseStatus(ResponseStatusCode::Success, 'Refund queued for processing'), $refundCreated->status);
        self::assertSame($orderId, $refundCreated->orderId);
        $refund = $refundCreated->refund;
        self::assertEquals(
            new ResponseRefund(
                '5000009987',
                '20200702091903',
                21000,
                'PLN',
                'Uznanie 5000009987 Refund',
                new \DateTimeImmutable('2020-07-02T09:19:03.896+02:00'),
                new \DateTimeImmutable('2020-07-02T09:19:04.013+02:00'),
                RefundStatus::Pending,
            ),
            $refund
        );
    }

    #[Test]
    public function errorTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(400, [], FileTestUtil::getFileContents(__DIR__ . '/data/refundErrorResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundRequest = new RefundRequest(
            new Refund(
                'Uznanie 5000009987 Refund',
                21000,
                extRefundId: '20200702091903'
            )
        );

        try {
            $this->getRefundRequestService()->create($orderId, $refundRequest, null);
        } catch (\Throwable $exception) {
            self::assertInstanceOf(PayURequestException::class, $exception);
            self::assertStringContainsString('ERROR_VALUE_MISSING', $exception->getMessage());
            self::assertSame(
                [
                    'status' => [
                        'statusCode' => 'ERROR_VALUE_MISSING',
                        'severity' => 'ERROR',
                        'code' => '8300',
                        'codeLiteral' => 'MISSING_REFUND_SECTION',
                        'statusDesc' => 'Missing required field',
                    ],
                ],
                $exception->response
            );

            return;
        }

        self::fail('Exception must be thrown.');
    }

    private function getRefundRequestService(): RefundRequestService
    {
        return new RefundRequestService(
            $this->getClient(),
            new NullLogger()
        );
    }
}
