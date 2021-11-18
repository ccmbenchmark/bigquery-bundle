<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Units\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class BigQueryExtension extends \atoum
{
    public function testLoadShouldNotRaiseError()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $this
            ->if($extension = new \CCMBenchmark\BigQueryBundle\DependencyInjection\BigQueryExtension())
                ->variable($extension->load([
                    [
                        'cloudstorage' => [
                            'bucket' => 'mybucket'
                        ],
                        'api' => [
                            'application_name' => 'myApp',
                            'credentials_file' => dirname(__FILE__) . '/../../Fixtures/key.json'
                        ],
                        'proxy' => []
                    ]
                ], $container))
                    ->isNull()
                        ->dump($container)
            ->exception(function() use ($container)
            {
                $container->get('Google_Client');
            })
                ->isInstanceOf(\DomainException::class)
                ->error()
                    ->exists()
        ;
    }
}
