<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\BigQuery;

use CCMBenchmark\BigQueryBundle\BigQuery\MetadataRepository;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Analytics;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Display;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\AnalyticsMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\DisplayMetadata;
use atoum;

class UnitOfWork extends atoum
{
    private $fileSystem;
    private $bigQueryClientMock;
    private $metadataRepository;
    private $jobFactory;

    /**
     * @var \CCMBenchmark\BigQueryBundle\BigQuery\UnitOfWork
     */
    private $instance;

    public function beforeTestMethod($method)
    {
        $this->fileSystem = new \mock\CCMBenchmark\BigQueryBundle\Tests\Fixtures\CloudStorage\DummyFileSystem();

        $this->jobFactory = new \CCMBenchmark\BigQueryBundle\BigQuery\JobFactory(
            $this->fileSystem,
            'test'
        );

        $this->mockGenerator()->orphanize('__construct');
        $googleClientMock = new \mock\Google_Client();

        $this->bigQueryClientMock = $this->newMockInstance(\Google_Service_Bigquery::class, null, null, [
            $googleClientMock
        ]);
        $this->bigQueryClientMock->jobs = new class{
            public $job = [];
            function insert($projectId, \Google_Service_Bigquery_Job $job) {
                $this->job[] = $job;
                return true;
            }
        };

        $this->metadataRepository = new MetadataRepository();
        $this->metadataRepository->addMetadata(
            new AnalyticsMetadata('myproject_analytics', 'mydataset_analytics')
        );
        $this->metadataRepository->addMetadata(
            new DisplayMetadata('myproject_display', 'mydataset_display')
        );


        $this->instance = new \CCMBenchmark\BigQueryBundle\BigQuery\UnitOfWork(
            $this->bigQueryClientMock,
            $this->metadataRepository,
            $this->jobFactory
        );
    }

    public function test Flush With 1 Data Type Should Call Store Once()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2019-04-09');
        $createdAt = new \DateTime();
        $this
            ->if($this->instance->addData(
                new Analytics(
                    $date,
                    'FR',
                    'google.com',
                    'mobile',
                    5000,
                    4000,
                    $createdAt
                )
            ))
            ->and($this->instance->addData(
                new Analytics(
                    $date,
                    'FR',
                    'google.com',
                    'mobile',
                    5000,
                    4000,
                    $createdAt
                )
            ))
            ->when($this->instance->flush())
            ->then()
                ->array($this->bigQueryClientMock->jobs->job)
                    ->hasSize(1)
        ;
    }

    public function test Flush With 2 Data Type Should Create 2 Jobs()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2019-04-09');
        $createdAt = new \DateTime();
        $this
            ->if($this->instance->addData(
                new Analytics(
                    $date,
                    'FR',
                    'google.com',
                    'mobile',
                    5000,
                    4000,
                    $createdAt
                )
            ))
            ->and($this->instance->addData(
                new Analytics(
                    $date,
                    'FR',
                    'google.com',
                    'mobile',
                    5000,
                    4000,
                    $createdAt
                )
            ))
            ->and($this->instance->addData(
                new Display(
                    $date,
                    'Google',
                    'type',
                    'FR',
                    'google.com',
                    'mobile',
                    'atf',
                    5000,
                    4000,
                    1000,
                    $createdAt
                )
            ))
            ->when($this->instance->flush())
            ->then()
                ->array($this->bigQueryClientMock->jobs->job)
                    ->hasSize(2)
        ;
    }
}
