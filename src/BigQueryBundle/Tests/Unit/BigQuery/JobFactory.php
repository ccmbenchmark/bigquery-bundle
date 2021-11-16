<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\BigQuery;

use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Analytics;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Display;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\AnalyticsMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata\DisplayMetadata;
use atoum;

class JobFactory extends atoum
{
    private $fileSystem;
    /**
     * @var \CCMBenchmark\BigQueryBundle\BigQuery\JobFactory
     */
    private $instance;

    public function beforeTestMethod($method)
    {
        $this->fileSystem = new \mock\CCMBenchmark\BigQueryBundle\Tests\Fixtures\CloudStorage\DummyFileSystem();

        $this->instance = new \CCMBenchmark\BigQueryBundle\BigQuery\JobFactory(
            $this->fileSystem,
            'test'
        );
    }

    public function test Create Job Should Return A Job()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2019-04-09');
        $createdAt = new \DateTime();

        $this
            ->object($this->instance->createJob(
                new AnalyticsMetadata('testProject', 'testDataset'),
                [
                    new Analytics(
                        $date,
                        'FR',
                        'google.com',
                        'mobile',
                        5000,
                        4000,
                        $createdAt
                    )
                ]
            ))
            ->isInstanceOf(\Google_Service_Bigquery_Job::class)
        ;
    }

    public function test Create Job Should Append Lines Using Newline Delimited JSON()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2019-04-09');
        $createdAt = new \DateTime();

        $this
            ->if($job = $this->instance->createJob(
                new AnalyticsMetadata('myproject_analytics', 'mydataset_analytics'),
                [
                    new Analytics(
                        $date,
                        'FR',
                        'google.com',
                        'mobile',
                        5000,
                        4000,
                        $createdAt
                    )
                ]
            ))
            /**
             * @var $load \Google_Service_Bigquery_JobConfigurationLoad
             */
            ->when($load = $job->getConfiguration()->getLoad())
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
            ->if($this->instance->createJob(
                new DisplayMetadata('myproject_display', 'mydataset_display'),
                [
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
                    ),
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
                ]
            ))
            ->string($this->fileSystem->data)
            ->isEqualTo(<<<JSONLD
{"date":"2019-04-09","country":"FR","site":"google.com","device":"mobile","format":"atf","requests":5000,"net_revenue":4000,"impressions":1000,"created_at":"2019-04-09 18:49:16","partner":"Google","type":"type"}
{"date":"2019-04-09","country":"FR","site":"google.com","device":"mobile","format":"atf","requests":5000,"clics":1000,"net_revenue":4000,"impressions":1000,"created_at":"2019-04-09 18:49:16","partner":"Google","type":"type","viewMeasuredImpressions":2000,"viewViewedImpressions":1500}
JSONLD
            )

        ;
    }
}
