<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\BigQuery;

use CCMBenchmark\BigQueryBundle\BigQuery\MetadataRepository;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Analytics;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Display;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\AnalyticsMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\DisplayMetadata;
use mageekguy\atoum;

class UnitOfWork extends atoum
{
    private $fileSystem;
    private $bigQueryClientMock;
    private $metadataRepository;
    /**
     * @var \CCMBenchmark\BigQueryBundle\BigQuery\UnitOfWork
     */
    private $instance;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->fileSystem = new \mock\CCMBenchmark\BigQueryBundle\Tests\Fixtures\CloudStorage\DummyFileSystem();
        $this->mockGenerator()->orphanize('__construct');
        $googleClientMock = new \mock\Google_Client();

        $this->bigQueryClientMock = $this->newMockInstance(\Google_Service_Bigquery::class, null, null, [
            $googleClientMock
        ]);
        $this->bigQueryClientMock->jobs = new class{
            public $job;
            function insert($projectId, \Google_Service_Bigquery_Job $job) {
                $this->job = $job;
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
            $this->fileSystem,
            'tests',
            $this->metadataRepository
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
            ->when($this->instance->flush())
            ->then
                ->mock($this->fileSystem)
                    ->call('store')
                        ->once()
        ;
    }

    public function test Flush With 2 Data Type Should Call Store Once Per Type()
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
            ->then
                ->mock($this->fileSystem)
                    ->call('store')
                        ->twice()
        ;
    }

    public function test Flush Should Create A Job()
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
            ->then($this->instance->flush())
            ->object($this->bigQueryClientMock->jobs->job)
                ->isInstanceOf(\Google_Service_Bigquery_Job::class)
        ;
    }

    public function test Flush Should Create A Properly Configured Job()
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
            ->then($this->instance->flush())
            /**
             * @var $load \Google_Service_Bigquery_JobConfigurationLoad
             */
            ->when($load = $this->bigQueryClientMock->jobs->job->getConfiguration()->getLoad())
                ->string($load->getWriteDisposition())
                    ->isEqualTo('WRITE_APPEND')
                ->string($load->getSourceFormat())
                    ->isEqualTo('NEWLINE_DELIMITED_JSON')
            ->and($table = $load->getDestinationTable())
                ->string($table->getProjectId())
                    ->isEqualTo('myproject_analytics')
                ->string($table->getTableId())
                    ->isEqualTo('analytics')
                ->string($table->getDatasetId())
                    ->isEqualTo('mydataset_analytics')
        ;
    }

    public function test Flush Should Create A Newline Delimited JSON And Strip Null Values()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2019-04-09');
        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-04-09 18:49:16');

        $this
            ->if($this->instance->addData(
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
                    $createdAt,
                    1000,
                    2000,
                    1500
                )
            ))
            ->then($this->instance->flush())
            ->string($this->fileSystem->data)
                ->isEqualTo(<<<JSONLD
{"date":"2019-04-09","country":"FR","site":"google.com","device":"mobile","format":"atf","requests":5000,"net_revenue":4000,"impressions":1000,"created_at":"2019-04-09 18:49:16","partner":"Google","type":"type"}
{"date":"2019-04-09","country":"FR","site":"google.com","device":"mobile","format":"atf","requests":5000,"clics":1000,"net_revenue":4000,"impressions":1000,"created_at":"2019-04-09 18:49:16","partner":"Google","type":"type","viewMeasuredImpressions":2000,"viewViewedImpressions":1500}
JSONLD
                )

        ;
    }
}
