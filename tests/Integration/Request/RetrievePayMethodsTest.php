<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Enum\PayByLinkStatus;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use Answear\Payum\PayU\ValueObject\Configuration;
use Answear\Payum\PayU\ValueObject\Response\PayByLink;
use Answear\Payum\PayU\ValueObject\Response\ResponseStatus;
use PHPUnit\Framework\TestCase;

class RetrievePayMethodsTest extends TestCase
{
    /**
     * @test
     */
    public function retrieveTest(): void
    {
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/authorisationResponse.json'));
        \OpenPayU_HttpCurl::addResponse(200, FileTestUtil::getFileContents(__DIR__ . '/data/payMethodsResponse.json'));

        $response = $this->getApiService()->retrievePayMethods('pl');
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