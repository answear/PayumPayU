<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\PayMethod;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByCreditCard;
use Answear\Payum\PayU\ValueObject\Response\OrderTransactions\ByPBL;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;

class OrderRetrieveTransactionsTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function retrieveByCardTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderRetrieveTransactionsByCard.json'))
        );

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getOrderRequestService()->retrieveTransactions($orderId, null);
        self::assertCount(1, $response);
        /** @var ByCreditCard $transaction */
        $transaction = $response[0];
        self::assertEquals(
            new PayMethod(null, 'c'),
            $transaction->getPayMethod()
        );
        self::assertSame('FIRST_ONE_CLICK_CARD', $transaction->paymentFlow);
        self::assertSame(
            [
                'cardData' => [
                    'cardNumberMasked' => '543402******4014',
                    'cardScheme' => 'MC',
                    'cardProfile' => 'CONSUMER',
                    'cardClassification' => 'DEBIT',
                    'cardResponseCode' => '000',
                    'cardResponseCodeDesc' => '000 - OK',
                    'cardEciCode' => '2',
                    'card3DsStatus' => 'Y',
                    'card3DsStatusDescription' => 'MessageVersion=2.1.0,browser flow,3DS method not available,dynamic authentication,no cancel indicator,no status reason',
                    'cardBinCountry' => 'PL',
                    'firstTransactionId' => 'MCC0111LL1121',
                ],
                'cardInstallmentProposal' => [
                    'proposalId' => '5aff3ba8-0c37-4da1-ba4a-4ff24bcc2eed',
                ],
            ],
            $transaction->card
        );
    }

    /**
     * @test
     */
    public function retrieveByPBLTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderRetrieveTransactionsByPBL.json'))
        );

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getOrderRequestService()->retrieveTransactions($orderId, null);
        self::assertCount(1, $response);
        /** @var ByPBL $transaction */
        $transaction = $response[0];
        self::assertEquals(
            new PayMethod(null, 'm'),
            $transaction->getPayMethod()
        );
        self::assertSame(
            [
                'number' => '80607787095718703296721164',
                'name' => 'JAN KOWALSKI',
                'city' => 'WARSZAWA',
                'postalCode' => '02-638',
                'street' => 'UL.NOWOWIEJSKIEGO 8',
                'address' => 'Warszawa Nowowiejskiego 8',
            ],
            $transaction->bankAccount
        );
    }

    private function getOrderRequestService(): OrderRequestService
    {
        return new OrderRequestService(
            $this->getConfigProvider(),
            $this->getClient(),
            new NullLogger()
        );
    }
}
