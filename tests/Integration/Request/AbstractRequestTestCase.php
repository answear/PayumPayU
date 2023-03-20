<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Client\Client;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Tests\MockGuzzleTrait;
use PHPUnit\Framework\TestCase;

abstract class AbstractRequestTestCase extends TestCase
{
    use MockGuzzleTrait;

    protected const CONFIG_KEY = 'pos_123';

    protected \GuzzleHttp\Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->setupGuzzleClient();
    }

    protected function getConfigProvider(): ConfigProvider
    {
        return new ConfigProvider(
            Environment::Secure->value,
            [
                self::CONFIG_KEY => [
                    'public_shop_id' => 'sas323',
                    'pos_id' => '12653',
                    'signature_key' => 'sign_key527',
                    'oauth_client_id' => '98274',
                    'oauth_secret' => 'secret@#$VFSDF',
                ],
            ]
        );
    }

    protected function getClient(): Client
    {
        return new Client(
            $this->getConfigProvider(),
            $this->client
        );
    }
}
