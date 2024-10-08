<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\RefundStatus;
use Answear\Payum\PayU\Enum\RefundStatusErrorCode;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayUNetworkException;
use Answear\Payum\PayU\Request\RefundRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\Refund;
use Answear\Payum\PayU\ValueObject\Response\RefundStatusError;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;

class RetrieveRefundsTest extends AbstractRequestTestCase
{
    #[Test]
    public function retrieveRefundListTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveRefundListResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';

        $refundList = $this->getRefundRequestService()->retrieveRefundList($orderId, null);
        self::assertIsArray($refundList);
        self::assertCount(2, $refundList);
        self::assertEquals(
            [
                new Refund(
                    '5000000142',
                    'ext_QX9ZR7M6QP200601GUEST000P01',
                    400,
                    'PLN',
                    'Zwrot transakcji BLIK',
                    new \DateTimeImmutable('2020-06-01T13:05:39.489+02:00'),
                    new \DateTimeImmutable('2020-06-01T13:06:03.530+02:00'),
                    RefundStatus::Finalized,
                ),
                new Refund(
                    '5000000143',
                    'ext_QX9ZR7M6QP200601GUEST000P01',
                    700,
                    'PLN',
                    'Zwrot transakcji P',
                    new \DateTimeImmutable('2020-06-01T13:18:03.648+02:00'),
                    new \DateTimeImmutable('2020-06-01T13:18:33.661+02:00'),
                    RefundStatus::Canceled,
                ),
            ],
            $refundList
        );
    }

    #[Test]
    public function retrieveSingleRefundTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveSingleRefundResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundId = '5000000142';

        $refund = $this->getRefundRequestService()->retrieveSingleRefund($orderId, $refundId, null);
        self::assertEquals(
            new Refund(
                '5000000108',
                'ext_QLQWXCSM1D200609GUEST000P01_1591707454318',
                999,
                'PLN',
                'Zwrot transakcji',
                new \DateTimeImmutable('2020-06-09T14:57:34.594+02:00'),
                new \DateTimeImmutable('2020-06-09T14:57:55.370+02:00'),
                RefundStatus::Canceled,
                new RefundStatusError(
                    RefundStatusErrorCode::ProviderTechnicalError,
                    RefundStatusErrorCode::ProviderTechnicalError->value,
                    'Temporary problem on Provider Side'
                )
            ),
            $refund
        );
    }

    #[Test]
    public function retrieveSingleRefundNotFoundTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(404, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveSingleRefundWithNotFoundResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundId = '5000400478-not-found';

        $throwException = false;
        try {
            $this->getRefundRequestService()->retrieveSingleRefund($orderId, $refundId, null);
        } catch (PayUNetworkException $exception) {
            $throwException = true;

            self::assertSame(404, $exception->getCode());
            self::assertSame(
                [
                    'status' => [
                        'statusCode' => ResponseStatusCode::DataNotFound->value,
                        'severity' => 'INFO',
                        'statusDesc' => 'Could not find refund [refundId=5000400478-not-found]',
                    ],
                ],
                $exception->response
            );
        }

        self::assertTrue($throwException, 'Expect exception.');
    }

    #[Test]
    public function retrieveEmptyRefundListTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/retrieveEmptyRefundListResponse.json'))
        );

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';

        $refundList = $this->getRefundRequestService()->retrieveRefundList($orderId, null);
        self::assertIsArray($refundList);
        self::assertCount(0, $refundList);
    }

    private function getRefundRequestService(): RefundRequestService
    {
        return new RefundRequestService(
            $this->getClient(),
            new NullLogger()
        );
    }
}
