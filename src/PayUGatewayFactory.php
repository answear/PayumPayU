<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Answear\Payum\PayU\Action\CaptureAction;
use Answear\Payum\PayU\Action\ConvertPaymentAction;
use Answear\Payum\PayU\Action\NotifyAction;
use Answear\Payum\PayU\Action\RefundAction;
use Answear\Payum\PayU\Action\StatusAction;
use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\PayULogger;
use Answear\Payum\PayU\ValueObject\Configuration;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;

class PayUGatewayFactory extends GatewayFactory
{
    private const FACTORY_NAME = 'payu';
    private const REQUIRED_OPTIONS = ['environment', 'pos_id', 'signature_key', 'oauth_client_id', 'oauth_secret'];

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => self::FACTORY_NAME,
                'payum.factory_title' => 'PayU',
                'payum.logger' => null,
                'payum.action.capture' => new CaptureAction(),
                'payum.action.refund' => new RefundAction(),
                'payum.action.notify' => new NotifyAction(),
                'payum.action.status' => new StatusAction(),
                'payum.action.convert_payment' => new ConvertPaymentAction(),
            ]
        );

        if (empty($config['payum.api'])) {
            $config['payum.default_options'] = [
                'environment' => Environment::Sandbox->value,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['configs'];

            $config['payum.api'] = static function (ArrayObject $config) {
                return self::getApiService($config);
            };
        }
    }

    private static function getApiService(ArrayObject $config): Api
    {
        $config->validateNotEmpty($config['payum.required_options']);
        $configs = new ArrayObject($config['configs']);
        if (0 === $configs->count()) {
            throw new LogicException('Empty PayU configuration.');
        }

        $emptyFields = [];
        foreach ($configs as $configName => $posConfig) {
            if (!is_string($configName)) {
                throw new LogicException(sprintf('Configuration key must be a string. "%s" provided', $configName));
            }
            if (preg_match('/[^a-z_\-0-9]/i', $configName)) {
                throw new LogicException(
                    sprintf('Invalid configuration key for config %s.', $configName)
                );
            }
            if (!is_array($posConfig)) {
                throw new LogicException(
                    sprintf('Invalid configuration for config %s.', $configName)
                );
            }
            foreach (self::REQUIRED_OPTIONS as $option) {
                if (empty($posConfig[$option])) {
                    $emptyFields[$option] = $option;
                }
            }

            if ($emptyFields) {
                throw new LogicException(
                    sprintf(
                        'The %s fields are required for config %s.',
                        implode(', ', $emptyFields),
                        $configName
                    )
                );
            }

            if (!Environment::hasValue($posConfig['environment'])) {
                throw new LogicException(
                    sprintf(
                        'Environment for config %s should be one of [%s].',
                        $configName,
                        implode(',', array_column(Environment::cases(), 'value'))
                    )
                );
            }
        }

        return new Api(
            array_map(
                static fn(array $config) => new Configuration(
                    Environment::from($config['environment']),
                    $config['pos_id'],
                    $config['signature_key'],
                    $config['oauth_client_id'],
                    $config['oauth_secret']
                ),
                $configs->toUnsafeArray()
            ),
            new PayULogger($config['payum.logger'] ?? null)
        );
    }
}
