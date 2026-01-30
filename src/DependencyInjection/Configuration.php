<?php

namespace Gibass\DomainMakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('domain_maker');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('parameters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('root_namespace')->defaultValue('App')->end()
                        ->arrayNode('dir')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('src')->defaultValue('%kernel.project_dir%/src/')->end()
                                ->scalarNode('config')->defaultValue('%kernel.project_dir%/config/')->end()
                                ->scalarNode('test')->defaultValue('%kernel.project_dir%/tests/')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
