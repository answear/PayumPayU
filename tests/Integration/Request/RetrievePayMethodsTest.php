<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Enum\PayByLinkStatus;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Request\PayMethodsRequestService;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Response\PayByLink;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;

class RetrievePayMethodsTest extends AbstractRequestTestCase
{
    /**
     * @test
     */
    public function retrieveTest(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/authorisationResponse.json'))
        );
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/payMethodsResponse.json'))
        );

        $response = $this->getPayMethodsRequestService()->retrieve(null, 'pl');
        self::assertCount(0, $response->cardTokens);
        self::assertCount(0, $response->pexTokens);
        self::assertCount(4, $response->payByLinks);
        self::assertEquals(new ResponseStatus(ResponseStatusCode::Success), $response->status);
        self::assertEquals(
            [
                new PayByLink(
                    'dpp',
                    'PayPo | PayU Płacę później',
                    'https://static.payu.com/images/mobile/logos/pbl_dpp.png',
                    PayByLinkStatus::Enabled,
                    1000,
                    200000
                ),
                new PayByLink(
                    'm',
                    'mTransfer',
                    'https://static.payu.com/images/mobile/logos/pbl_m.png',
                    PayByLinkStatus::Enabled,
                    50,
                    null
                ),
                new PayByLink(
                    'o',
                    'Płacę z Bankiem Pekao S.A.',
                    'https://static.payu.com/images/mobile/logos/pbl_o.png',
                    PayByLinkStatus::Disabled,
                    50,
                    null
                ),
                new PayByLink(
                    'c',
                    'Płatność online kartą płatniczą',
                    'https://static.payu.com/images/mobile/logos/pbl_c.png',
                    PayByLinkStatus::TemporaryDisabled,
                ),
            ],
            $response->payByLinks
        );
    }

    /**
     * @test
     */
    public function exceptionIfNoSuccess(): void
    {
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/authorisationResponse.json'))
        );
        $this->mockGuzzleResponse(
            new Response(200, [], FileTestUtil::getFileContents(__DIR__ . '/data/payMethodsError.json'))
        );

        $withException = false;
        try {
            $this->getPayMethodsRequestService()->retrieve(null, '');
        } catch (PayURequestException $exception) {
            self::assertSame('Getting pay methods failed.', $exception->getMessage());
            self::assertSame(
                [
                    'status' => [
                        'statusCode' => 'ERROR_VALUE_INVALID',
                        'statusDesc' => 'Wrong lang parameter value. Use ISO 639-1 codes.',
                    ],
                ],
                $exception->response
            );
            $withException = true;
        }

        self::assertTrue($withException);
    }

    private function getPayMethodsRequestService(): PayMethodsRequestService
    {
        return new PayMethodsRequestService(
            $this->getClient(),
            new NullLogger()
        );
    }
}
