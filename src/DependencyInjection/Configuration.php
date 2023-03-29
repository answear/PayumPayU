<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\DependencyInjection;

use Answear\Payum\PayU\Enum\Environment;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('answear_payum_payu');

        $environments = array_map(static fn(Environment $environment) => $environment->value, Environment::cases());
        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('environment')
                    ->values($environments)
                ->isRequired()
                ->end()
                ?->arrayNode('configs')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ?->scalarNode('public_shop_id')->isRequired()->end()
                            ?->scalarNode('pos_id')->isRequired()->end()
                            ?->scalarNode('signature_key')->isRequired()->end()
                            ?->scalarNode('oauth_client_id')->isRequired()->end()
                            ?->scalarNode('oauth_secret')->isRequired()->end()
                        ?->end()
                    ->end()
                ->end()
                ?->scalarNode('logger')->defaultValue(null)->end()
            ?->end();

        return $treeBuilder;
    }
}
