<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Request;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\PayULogger;
use Answear\Payum\PayU\ValueObject\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

abstract class AbstractRequestTest extends TestCase
{
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
