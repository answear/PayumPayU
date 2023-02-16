<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\PayULogger;
use Answear\Payum\PayU\ValueObject\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class AbstractRequestTestCase extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        \OpenPayU_Configuration::setEnvironment();
        \OpenPayU_Configuration::setMerchantPosId('');
        \OpenPayU_Configuration::setSignatureKey('');
        \OpenPayU_Configuration::setOauthClientId('');
        \OpenPayU_Configuration::setOauthClientSecret('');
        \OpenPayU_Configuration::setOauthGrantType(\OauthGrantType::CLIENT_CREDENTIAL);
        \OpenPayU_Configuration::setOauthEmail('');
        \OpenPayU_Configuration::setOauthExtCustomerId('');

        \OpenPayU_HttpCurl::clearHistory();
    }

    protected function getApiService(): Api
    {
        return new Api(
            [
                'one_pos' => new Configuration(
                    Environment::Sandbox,
                    'public_shop_id',
                    '123',
                    's-key',
                    'cl-id',
                    'sec',
                ),
            ],
            new PayULogger(new NullLogger())
        );
    }
}
