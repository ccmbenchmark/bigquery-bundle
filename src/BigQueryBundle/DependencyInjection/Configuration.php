<?php

namespace CCMBenchmark\BigQueryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('big_query');
        $rootNode
            ->children()
                ->arrayNode('cloudstorage')
                    ->isRequired()
                    ->children()
                        ->scalarNode('bucket')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Name of the bucket on Google Cloud Storage')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('api')
                ->isRequired()
                    ->children()
                        ->scalarNode('application_name')
                            ->info('Name of the api client')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('credentials_file')
                            ->info('Path to the credentials file, in json format')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('proxy')
                    ->children()
                        ->scalarNode('host')
                            ->info('If applicable, the host proxy to connect to google apis')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('port')
                            ->info('If applicable, the host port to connect to google apis')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
