<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PayUGatewayFactory extends GatewayFactory
{
    private const FACTORY_NAME = 'payu';
    private const REQUIRED_SERVICES = [
        'payum.action.capture',
        'payum.action.refund',
        'payum.action.notify',
        'payum.action.status',
        'payum.action.convert_payment',
        'payum.action.sync_payment',
    ];

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => self::FACTORY_NAME,
                'payum.factory_title' => 'PayU',
            ]
        );

        $config->validateNotEmpty(self::REQUIRED_SERVICES);
    }
}
