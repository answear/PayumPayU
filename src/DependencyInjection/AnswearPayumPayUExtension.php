<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\DependencyInjection;

use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Service\PayULogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AnswearPayumPayUExtension extends Extension implements PrependExtensionInterface
{
    private ?Definition $loggerDefinition = null;

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        if (isset($configs[0]['logger'])) {
            $this->loggerDefinition = $container->getDefinition($configs[0]['logger']);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->setConfigProvider(
            $container,
            $config['environment'],
            $config['configs']
        );
        if (isset($config['logger']) && null === $this->loggerDefinition) {
            $this->loggerDefinition = $container->getDefinition($config['logger']);
        }
        $this->setLogger($container, $this->loggerDefinition ?? null);
    }

    private function setConfigProvider(ContainerBuilder $container, string $environment, array $configs): void
    {
        $definition = $container->getDefinition(ConfigProvider::class);
        $definition->setArguments([$environment, $configs]);
    }

    private function setLogger(ContainerBuilder $container, ?Definition $loggerDefinition): void
    {
        $definition = $container->getDefinition(PayULogger::class);
        $definition->setArguments([$loggerDefinition]);
    }
}
