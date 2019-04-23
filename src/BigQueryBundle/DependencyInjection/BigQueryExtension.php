<?php

namespace CCMBenchmark\BigQueryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class BigQueryExtension extends ConfigurableExtension implements CompilerPassInterface
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $jobDefinition = $container->autowire(\CCMBenchmark\BigQueryBundle\BigQuery\JobFactory::class, \CCMBenchmark\BigQueryBundle\BigQuery\JobFactory::class);
        $jobDefinition
            ->setArgument('$bucket', $mergedConfig['cloudstorage']['bucket'])
        ;
        $clientDefinition = $container->autowire(\CCMBenchmark\BigQueryBundle\ClientFactory::class, \CCMBenchmark\BigQueryBundle\ClientFactory::class);
        $clientDefinition
            ->setArgument('$applicationName', $mergedConfig['api']['application_name'])
            ->setArgument('$credentialsFile', $mergedConfig['api']['credentials_file'])
            ->setArgument('$proxy', [
                'host' => $mergedConfig['proxy']['host'],
                'port' => $mergedConfig['proxy']['port'],
            ])
        ;
        $container->autowire(\CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem::class, \CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem::class);
        $loader->load('services.xml');
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $metadataDefinition = $container->autowire(\CCMBenchmark\BigQueryBundle\BigQuery\MetadataRepository::class, \CCMBenchmark\BigQueryBundle\BigQuery\MetadataRepository::class);
        $metadata = $container->findTaggedServiceIds('big_query.metadata');
        foreach ($metadata as $serviceId => &$tags) {
            if (count($tags) > 1) {
                throw new InvalidConfigurationException(sprintf(
                    'A metadata can only have one tag named "%s". Please check your service "%s".',
                    'big_query.metadata',
                    $serviceId
                ));
            }
            $metadataDefinition->addMethodCall('addMetadata', [new Reference($serviceId)]);
        }
    }
}
