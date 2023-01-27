<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Enum\RefundStatus;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Request\Refund\Refund;
use Answear\Payum\PayU\ValueObject\Request\RefundRequest;
use Answear\Payum\PayU\ValueObject\Response\RefundCreated\Refund as ResponseRefund;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use PHPUnit\Framework\TestCase;

class CreateRefundTest extends TestCase
{
    /**
     * @test
     */
    public function createTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/refundCreatedResponse.json'));

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundRequest = new RefundRequest(
            new Refund(
                'Uznanie 5000009987 Refund',
                21000,
                extRefundId: '20200702091903'
            )
        );

        $refundCreated = $this->getApiService()->createRefund($orderId, $refundRequest);
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
                RefundStatus::Pending,
                new \DateTimeImmutable('2020-07-02T09:19:04.013+02:00'),
            ),
            $refund
        );
    }

    /**
     * @test
     */
    public function errorTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(400, FileTestUtil::getFileContents(__DIR__ . '/data/refundErrorResponse.json'));

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundRequest = new RefundRequest(
            new Refund(
                'Uznanie 5000009987 Refund',
                21000,
                extRefundId: '20200702091903'
            )
        );

        $this->expectException(\OpenPayU_Exception_Request::class);
        $this->expectExceptionMessage('ERROR_VALUE_MISSING - Missing required field');
        $this->getApiService()->createRefund($orderId, $refundRequest);
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
