<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\ValueObject\Configuration;
use Webmozart\Assert\Assert;

class ConfigProvider
{
    public readonly Configuration $configuration;

    public function __construct(array $configsArray)
    {
        foreach ($configsArray as $configArray) {
            $this->configuration[] = new Configuration(
                Environment::from($configArray['environment']),
                $configArray['public_shop_id'],
                $configArray['pos_id'],
                $configArray['signature_key'],
                $configArray['oauth_client_id'],
                $configArray['oauth_secret'],
            );
        }

        Assert::notEmpty($this->configuration);
    }
}
