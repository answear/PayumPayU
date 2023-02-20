<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\RefundStatus;
use Answear\Payum\PayU\Enum\RefundStatusErrorCode;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\Refund;
use Answear\Payum\PayU\ValueObject\Response\RefundStatusError;

class RetrieveRefundsTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function retrieveRefundListTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/retrieveRefundListResponse.json'));

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';

        $refundList = $this->getApiService()->retrieveRefundList($orderId, null);
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

    /**
     * @test
     */
    public function retrieveSingleRefundTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/retrieveSingleRefundResponse.json'));

        $orderId = 'ZXWZ53KQQM200702GUEST000P01';
        $refundId = '5000000142';

        $refundList = $this->getApiService()->retrieveSingleRefund($orderId, $refundId, null);
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
            $refundList
        );
    }
}
