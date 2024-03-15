<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayUAuthorizationException;
use Answear\Payum\PayU\Exception\PayUNetworkException;
use Answear\Payum\PayU\Exception\PayUServerErrorException;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;

class CancelOrderTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function createTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderCanceledResponse.json'))
        );

        $orderId = 'WZHF5FFDRJ140731GUEST000P01';
        $response = $this->getOrderRequestService()->cancel($orderId, null);

        self::assertEquals(
            new ResponseStatus(
                ResponseStatusCode::Success,
                'Request processing successful'
            ),
            $response->status
        );
        self::assertSame($orderId, $response->orderId);
        self::assertSame('extOrderId123', $response->extOrderId);
    }

    /**
     * @test
     */
    public function createUnauthorizedTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(401, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderUnauthorizedResponse.json'))
        );

        $this->expectException(PayUAuthorizationException::class);
        $this->expectExceptionCode(401);
        $this->getOrderRequestService()->cancel('WZHF5FFDRJ140731GUEST000P01', null);
    }

    /**
     * @test
     */
    public function notFoundTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(404, [], FileTestUtil::getFileContents(__DIR__ . '/data/orderNotFoundForCancelResponse.json'))
        );

        $this->expectException(PayUNetworkException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageMatches('/DATA_NOT_FOUND/');

        $this->getOrderRequestService()->cancel('WZHF5FFDRJ140731GUEST000P01', null);
    }

    /**
     * @test
     */
    public function internalServerErrorTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(500, [], FileTestUtil::getFileContents(__DIR__ . '/data/internalServerErrorResponse.json'))
        );

        $this->expectException(PayUServerErrorException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessageMatches('/OPENPAYU_ERROR_INTERNAL/');

        $this->getOrderRequestService()->cancel('WZHF5FFDRJ140731GUEST000P01', null);
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
