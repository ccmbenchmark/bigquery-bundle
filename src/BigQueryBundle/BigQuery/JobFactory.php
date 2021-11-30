<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystemInterface;

class JobFactory
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $bucket;

    public function __construct(
        FileSystemInterface $fileSystem,
        $bucket
    )
    {
        $this->fileSystem = $fileSystem;
        $this->bucket = $bucket;
    }

    public function createJob(MetadataInterface $metadata, array $data): \Google\Service\Bigquery\Job
    {
        $job = new \Google\Service\Bigquery\Job();

        $name = 'reporting-' . date('Y-m-d') . '-' . uniqid() . '.json';

        /*
         * When importing data into bigquery, via a json file, null fields must be omitted. Otherwise it'll try to treat
         * this field as the string "null".
         * @see {https://stackoverflow.com/a/32619240}
         * So we need to explicitly call jsonSerialize and strip null values in this array
         */
        array_walk(
            $data,
            function(\JsonSerializable &$item) {
                $item = array_filter(
                    $item->jsonSerialize(),
                    function($value) {
                        return $value !== null;
                    }
                );
            }
        );

        // We create a file on google cloud storage and then leave it here, so we can inspect it if needed.
        $this->fileSystem->store(
            $this->bucket,
            $name,
            'application/json',
            implode(PHP_EOL, array_map('json_encode', $data))  // NewLine delimited JSON
        );

        $jobConfiguration = new \Google\Service\Bigquery\JobConfiguration();
        $jobConfigurationLoad = new \Google\Service\Bigquery\JobConfigurationLoad();
        $jobConfigurationLoad->setSourceUris(['gs://' . $this->bucket . '/' . $name]);

        $reference = new \Google\Service\Bigquery\TableReference();
        $reference->setDatasetId($metadata->getDatasetId());
        $reference->setTableId($metadata->getTableId());
        $reference->setProjectId($metadata->getProjectId());

        $jobConfigurationLoad->setDestinationTable($reference);
        $jobConfigurationLoad->setSourceFormat("NEWLINE_DELIMITED_JSON");
        $jobConfigurationLoad->setWriteDisposition('WRITE_APPEND');

        $schema = new \Google\Service\Bigquery\TableSchema();
        $schema->setFields($metadata->getSchema());
        $jobConfigurationLoad->setSchema($schema);

        /**
         * We create a partition per day, using the column "date" from our dataset.
         */
        $timePartitioning = new \Google\Service\Bigquery\TimePartitioning();
        $timePartitioning->setType('DAY');
        $timePartitioning->setField('date');
        $jobConfigurationLoad->setTimePartitioning($timePartitioning);
        $jobConfiguration->setLoad($jobConfigurationLoad);

        $job->setConfiguration($jobConfiguration);

        return $job;
    }
}
