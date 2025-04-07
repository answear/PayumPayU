<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit\Service;

use Answear\Payum\PayU\Service\UserIpService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserIpServiceTest extends TestCase
{
    #[DataProvider('provideDataToTest')]
    #[Test]
    public function ipTest(array $serverParams, ?string $expectedIp): void
    {
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['HTTP_TRUE_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);

        foreach ($serverParams as $key => $value) {
            $_SERVER[$key] = $value;
        }

        self::assertSame(
            $expectedIp,
            (new UserIpService())->getIp()
        );
    }

    public static function provideDataToTest(): iterable
    {
        yield [
            ['HTTP_CF_CONNECTING_IP' => '127.0.0.1'],
            '127.0.0.1',
        ];

        yield [
            [
                'HTTP_CF_CONNECTING_IP' => '127.0.0.1',
                'HTTP_TRUE_CLIENT_IP' => '127.0.0.2',
                'HTTP_X_FORWARDED_FOR' => '127.0.0.3',
                'REMOTE_ADDR' => '127.0.0.4',
            ],
            '127.0.0.1',
        ];

        yield [
            ['HTTP_TRUE_CLIENT_IP' => '127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '127.0.0.3', 'REMOTE_ADDR' => '127.0.0.4'],
            '127.0.0.2',
        ];

        yield [
            ['HTTP_X_FORWARDED_FOR' => '127.0.0.3', 'REMOTE_ADDR' => '127.0.0.4'],
            '127.0.0.3',
        ];

        yield [
            ['REMOTE_ADDR' => '127.0.0.4'],
            '127.0.0.4',
        ];

        yield [
            [],
            null,
        ];

        yield [
            ['HTTP_X_FORWARDED_FOR' => '127.0.0.3, 127.0.0.4'],
            '127.0.0.3',
        ];
    }
}
