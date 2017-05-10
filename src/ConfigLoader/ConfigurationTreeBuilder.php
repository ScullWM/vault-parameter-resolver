<?php

namespace IED\VaultParameterResolver\ConfigLoader;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTreeBuilder implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('vault');
        $rootNode
            ->children()
                ->scalarNode('host')->isRequired()->end()
                ->arrayNode('auth')
                ->validate()
                    ->ifTrue(function($v) { return count($v) !== 1; })
                        ->thenInvalid('You must define exactly one auth backend.')
                    ->end()
                    ->children()
                        ->arrayNode('app_role')
                            ->children()
                                ->scalarNode('role_id')->isRequired()->end()
                                ->scalarNode('secret_id')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ;

        return $treeBuilder;
    }
}
