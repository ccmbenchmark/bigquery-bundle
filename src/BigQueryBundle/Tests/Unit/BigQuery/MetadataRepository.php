<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\BigQuery;

use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Analytics;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Display;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\AnalyticsMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\DisplayMetadata;
use atoum;

class MetadataRepository extends atoum
{
    public function test I Should Find Metadata For An Entity I Declared()
    {
        $this
            ->if($metadataRepository = new \CCMBenchmark\BigQueryBundle\BigQuery\MetadataRepository())
            ->and($analyticsMetadata = new AnalyticsMetadata('project_analytics', 'dataset_analytics'))
                ->then($metadataRepository->addMetadata($analyticsMetadata))
            ->and($displayMetadata = new DisplayMetadata('project_display', 'dataset_display'))
                ->then($metadataRepository->addMetadata($displayMetadata))
            ->object($metadataRepository->getMetadataForEntity(new Display(
                new \DateTime(),
                'Google',
                'type',
                'FR',
                'google.com',
                'mobile',
                'atf',
                5000,
                4000,
                1000,
                new \DateTime()
            )))
                ->isEqualTo($displayMetadata)
            ->object($metadataRepository->getMetadataForEntity(new Analytics(
                new \DateTime(),
                'FR',
                'google.com',
                'mobile',
                5000,
                4000,
                new \DateTime()
            )))
                ->isEqualTo($analyticsMetadata)
        ;
    }
}
