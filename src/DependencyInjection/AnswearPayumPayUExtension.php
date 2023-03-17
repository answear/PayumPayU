<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\DependencyInjection;

use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Service\PayULogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AnswearPayumPayUExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition(ConfigProvider::class);
        $definition->setArguments([$config['environment'], $config['configs']]);

        $this->setLogger($container, $config['logger'] ?? null);
    }

    private function setLogger(ContainerBuilder $container, ?string $loggerId): void
    {
        $logger = null;
        if (null !== $loggerId) {
            $logger = $container->getDefinition($loggerId);
        }

        $definition = $container->getDefinition(PayULogger::class);
        $definition->setArguments([$logger]);
    }
}
