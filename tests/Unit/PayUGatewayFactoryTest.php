<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit;

use Answear\Payum\PayU\Api;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\PayUGatewayFactory;
use Answear\Payum\PayU\Tests\Util\OverrideObjectPropertyUtil;
use Answear\Payum\PayU\ValueObject\Configuration;
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

        $this->assertInstanceOf(\Closure::class, $response['payum.api']);
        $api = $response['payum.api'](new ArrayObject($response));
        $this->assertInstanceOf(Api::class, $api);

        self::assertSame(array_keys($config['configs']), array_keys(OverrideObjectPropertyUtil::getValue($api, 'configurations')));
        self::assertEquals(
            array_map(
                fn(array $config) => new Configuration(
                    Environment::from($config['environment']),
                    $config['public_shop_id'],
                    $config['pos_id'],
                    $config['signature_key'],
                    $config['oauth_client_id'],
                    $config['oauth_secret']
                ),
                $config['configs']
            ),
            OverrideObjectPropertyUtil::getValue($api, 'configurations')
        );
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
            new LogicException('The configs fields are required.'),
        ];

        yield 'no keys' => [
            ['configs' => []],
            new LogicException('The configs fields are required.'),
        ];

        yield 'invalid key' => [
            [
                'configs' => [
                    'one pos' => [],
                ],
            ],
            new LogicException('Invalid configuration key for config one pos.'),
        ];

        yield 'no fields' => [
            [
                'configs' => [
                    'one_pos' => [
                        'environment' => 'secure',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                    ],
                ],
            ],
            new LogicException('The public_shop_id, oauth_client_id, oauth_secret fields are required for config one_pos.'),
        ];

        yield 'invalid env' => [
            [
                'configs' => [
                    'one_pos' => [
                        'environment' => 'invalid-env',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],
                ],
            ],
            new LogicException('Environment for config one_pos should be one of [custom,sandbox,secure].'),
        ];

        yield 'no fields on second' => [
            [
                'configs' => [
                    'one_pos' => [
                        'environment' => 'secure',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],

                    'two_pos' => [
                        'environment' => 'sandbox',
                        'public_shop_id' => 'public_shop_id',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],
                ],
            ],
            new LogicException('The pos_id fields are required for config two_pos.'),
        ];

        yield 'no string keys' => [
            [
                'configs' => [
                    [
                        'environment' => 'secure',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],
                ],
            ],
            new LogicException('Configuration key must be a string. "0" provided'),
        ];
    }

    public function provideValidConfig(): iterable
    {
        yield 'simple' => [
            [
                'configs' => [
                    'one_pos' => [
                        'environment' => 'secure',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],
                ],
            ],
        ];

        yield 'two' => [
            [
                'configs' => [
                    'first_pos' => [
                        'environment' => 'secure',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '123',
                        'signature_key' => 's-key',
                        'oauth_client_id' => 'cl-id',
                        'oauth_secret' => 'sec',
                    ],
                    'second_pos' => [
                        'environment' => 'sandbox',
                        'public_shop_id' => 'public_shop_id',
                        'pos_id' => '1232',
                        'signature_key' => 's-key2',
                        'oauth_client_id' => 'cl-id3',
                        'oauth_secret' => 'sec4',
                    ],
                ],
            ],
        ];
    }

    private function getFactory(): PayUGatewayFactory
    {
        return new PayUGatewayFactory();
    }
}
