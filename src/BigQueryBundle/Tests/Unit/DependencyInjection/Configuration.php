<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Units\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends \atoum
{
    public function testGetConfigTreeBuilderShouldReturnATree()
    {
        $this
            ->if($configuration = new \CCMBenchmark\BigQueryBundle\DependencyInjection\Configuration())
            ->object($configuration->getConfigTreeBuilder())
                ->isInstanceOf(TreeBuilder::class)
        ;
    }
}
