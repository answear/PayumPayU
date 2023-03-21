<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\ValueObject\Configuration;
use Webmozart\Assert\Assert;

class ConfigProvider
{
    private const OAUTH_CONTEXT = 'pl/standard/user/oauth/authorize';
    private const DOMAIN_PART = 'payu.com/';
    private const API_PART = 'api/';
    private const API_VERSION_PART = 'v2_1/';

    private readonly Environment $environment;
    /** @var Configuration[] */
    private readonly array $configurations;
    private readonly ?string $defaultConfigKey;

    /**
     * @param array<string, array> $configsArray
     */
    public function __construct(string $environment, array $configsArray)
    {
        $this->environment = Environment::from($environment);
        $configurationArray = [];
        foreach ($configsArray as $configKey => $configArray) {
            $configurationArray[$configKey] = new Configuration(
                $configArray['public_shop_id'],
                $configArray['pos_id'],
                $configArray['signature_key'],
                $configArray['oauth_client_id'],
                $configArray['oauth_secret'],
            );
        }
        Assert::notEmpty($configurationArray);

        $this->configurations = $configurationArray;
        $this->defaultConfigKey = (1 === \count($configurationArray)) ? array_key_first($configurationArray) : null;
    }

    public function getConfig(?string $configKey): Configuration
    {
        if (null === $this->defaultConfigKey && null === $configKey) {
            throw new \InvalidArgumentException('Config key must be provided.');
        }

        if (!isset($this->configurations[$configKey ?? $this->defaultConfigKey])) {
            throw new \RuntimeException(sprintf('Invalid config key %s provided.', $configKey ?? $this->defaultConfigKey));
        }

        return $this->configurations[$configKey ?? $this->defaultConfigKey];
    }

    public function getServiceUrl(): string
    {
        return match ($this->environment) {
            Environment::Secure => 'https://secure.' . self::DOMAIN_PART . self::API_PART . self::API_VERSION_PART,
            Environment::Sandbox => 'https://secure.snd.' . self::DOMAIN_PART . self::API_PART . self::API_VERSION_PART,
        };
    }

    public function getOAuthEndpoint(): string
    {
        return match ($this->environment) {
            Environment::Secure => 'https://secure.' . self::DOMAIN_PART . self::OAUTH_CONTEXT,
            Environment::Sandbox => 'https://secure.snd.' . self::DOMAIN_PART . self::OAUTH_CONTEXT,
        };
    }
}
