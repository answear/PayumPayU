<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Acceptance\DependencyInjection;

use Answear\Payum\PayU\DependencyInjection\AnswearPayumPayUExtension;
use Answear\Payum\PayU\DependencyInjection\Configuration;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\ConfigProvider;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @dataProvider provideValidConfig
     */
    public function validTest(array $configs): void
    {
        $this->assertConfigurationIsValid($configs);

        $extension = $this->getExtension();

        $builder = new ContainerBuilder();
        $extension->load($configs, $builder);

        $configProviderDefinition = $builder->getDefinition(ConfigProvider::class);

        self::assertSame($configs[0]['configs'], $configProviderDefinition->getArgument(0));
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidConfig
     */
    public function invalid(array $config, ?string $expectedMessage = null): void
    {
        $this->assertConfigurationIsInvalid(
            $config,
            $expectedMessage
        );
    }

    /**
     * @test
     *
     * @dataProvider provideMoreInvalidConfig
     */
    public function moreInvalidTest(array $configs, \Throwable $expectedException): void
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $this->assertConfigurationIsValid($configs);

        $extension = $this->getExtension();

        $builder = new ContainerBuilder();
        $extension->load($configs, $builder);
    }

    public function provideInvalidConfig(): iterable
    {
        yield [
            [
                [],
            ],
            '"answear_payum_payu" must be configured.',
        ];

        yield [
            [
                [
                    'apiKey' => 'apiKeyString',
                ],
            ],
            'Unrecognized option "apiKey" under "answear_payum_payu". Available options are "configs", "logger".',
        ];

        yield [
            [
                [
                    'configs' => 'config',
                ],
            ],
            'Invalid type for path "answear_payum_payu.configs"',
        ];

        yield [
            [
                [
                    'configs' => [],
                ],
            ],
            'The path "answear_payum_payu.configs" should have at least 1 element(s) defined.',
        ];

        yield [
            [
                [
                    'configs' => [
                        [
                            'environment' => 'sandbox',
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                    ],
                ],
            ],
            'The attribute "name" must be set for path "answear_payum_payu.configs".',
        ];

        yield [
            [
                [
                    'configs' => [
                        'pos_key' => [
                            'environment' => 'sandbox',
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                        ],
                    ],
                ],
            ],
            'The child config "oauth_secret" under "answear_payum_payu.configs.pos_key" must be configured.',
        ];

        yield [
            [
                [
                    'configs' => [
                        'pos_key' => [
                            'environment' => 'sandbox',
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                        'second_pos_key' => [
                            'environment' => 'sandbox',
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                    ],
                ],
            ],
            'The child config "signature_key" under "answear_payum_payu.configs.second_pos_key" must be configured.',
        ];
    }

    public function provideMoreInvalidConfig(): iterable
    {
        yield [
            [
                [
                    'configs' => [
                        'pos_key' => [
                            'environment' => 'sandbox',
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                    ],
                    'logger' => 'not-existing-service-name',
                ],
            ],
            new ServiceNotFoundException('not-existing-service-name'),
        ];
    }

    public function provideValidConfig(): iterable
    {
        yield [
            [
                [
                    'configs' => [
                        'first_pos' => [
                            'environment' => Environment::Sandbox->value,
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                    ],
                ],
            ],
        ];

        yield [
            [
                [
                    'configs' => [
                        'first_pos' => [
                            'environment' => Environment::Secure->value,
                            'public_shop_id' => 'sas323',
                            'pos_id' => 12653,
                            'signature_key' => 'sign_key527',
                            'oauth_client_id' => 98274,
                            'oauth_secret' => 'secret@#$VFSDF',
                        ],
                        'second_pos' => [
                            'environment' => Environment::Secure->value,
                            'public_shop_id' => 'sass323',
                            'pos_id' => '12653',
                            'signature_key' => 'signd_key527',
                            'oauth_client_id' => 982574,
                            'oauth_secret' => 'secrets@#$VFSDF',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [$this->getExtension()];
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    private function getExtension(): AnswearPayumPayUExtension
    {
        return new AnswearPayumPayUExtension();
    }
}
