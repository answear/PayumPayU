<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit;

use Answear\Payum\PayU\Action\CaptureAction;
use Answear\Payum\PayU\Action\ConvertPaymentAction;
use Answear\Payum\PayU\Action\NotifyAction;
use Answear\Payum\PayU\Action\RefundAction;
use Answear\Payum\PayU\Action\StatusAction;
use Answear\Payum\PayU\Action\SyncPaymentAction;
use Answear\Payum\PayU\PayUGatewayFactory;
use Answear\Payum\PayU\Tests\Util\OverrideObjectPropertyUtil;
use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class PayUGatewayFactoryTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider provideValidConfig
     */
    public function validConfigurationTest(array $config): void
    {
        $config['payum.http_client'] = $this->createMock(HttpClientInterface::class);
        $config['httplug.message_factory'] = $this->createMock(MessageFactory::class);
        $response = $this->getFactory()->createConfig($config);

        self::assertSame(
            [
                'payu',
                'PayU',
            ],
            [
                $response['payum.factory_name'],
                $response['payum.factory_title'],
            ]
        );

        self::assertArrayHasKey('payum.action.capture', $response);
        self::assertArrayHasKey('payum.action.refund', $response);
        self::assertArrayHasKey('payum.action.notify', $response);
        self::assertArrayHasKey('payum.action.status', $response);
        self::assertArrayHasKey('payum.action.convert_payment', $response);
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidConfig
     */
    public function invalidConfigurationTest(array $config, \Throwable $expectedException): void
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $config['payum.http_client'] = $this->createMock(HttpClientInterface::class);
        $config['httplug.message_factory'] = $this->createMock(MessageFactory::class);
        $response = $this->getFactory()->createConfig($config);

        $this->assertInstanceOf(\Closure::class, $response['payum.api']);
        $api = $response['payum.api'](new ArrayObject($response));
        $this->assertInstanceOf(Api::class, $api);

        self::assertSame(
            [$config['configs']],
            [
                OverrideObjectPropertyUtil::getValue($api, 'configurations'),
            ]
        );
    }

    public function provideInvalidConfig(): iterable
    {
        yield 'no configs' => [
            [],
            new LogicException('The payum.action.capture, payum.action.refund, payum.action.notify, payum.action.status, payum.action.convert_payment, payum.action.sync_payment fields are required.'),
        ];

        yield 'no fields' => [
            [
                'payum.action.capture' => $this->createMock(CaptureAction::class),
                'payum.action.refund' => $this->createMock(RefundAction::class),
                'payum.action.notify' => $this->createMock(NotifyAction::class),
                'payum.action.status' => $this->createMock(StatusAction::class),
                'payum.action.convert_payment' => $this->createMock(ConvertPaymentAction::class),
            ],
            new LogicException('The payum.action.sync_payment fields are required.'),
        ];
    }

    public function provideValidConfig(): iterable
    {
        yield 'simple' => [
            [
                'payum.action.capture' => $this->createMock(CaptureAction::class),
                'payum.action.refund' => $this->createMock(RefundAction::class),
                'payum.action.notify' => $this->createMock(NotifyAction::class),
                'payum.action.status' => $this->createMock(StatusAction::class),
                'payum.action.convert_payment' => $this->createMock(ConvertPaymentAction::class),
                'payum.action.sync_payment' => $this->createMock(SyncPaymentAction::class),
            ],
        ];
    }

    private function getFactory(): PayUGatewayFactory
    {
        return new PayUGatewayFactory();
    }
}
