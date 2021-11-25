<?php


namespace CCMBenchmark\BigQueryBundle\BigQuery;


use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;
use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem;
use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystemInterface;
use Google_Service_Bigquery_QueryRequest;

class UnitOfWork
{
    /**
     * @var \Google_Service_Bigquery
     */
    private $bigQueryClient;

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    /**
     * @var JobFactory
     */
    private $jobFactory;

    private $data = [];

    public function __construct(
        \Google_Service_Bigquery $bigQueryClient,
        MetadataRepository $metadataRepository,
        JobFactory $jobFactory
    ) {
        $this->bigQueryClient = $bigQueryClient;
        $this->metadataRepository = $metadataRepository;
        $this->jobFactory = $jobFactory;
    }

    /**
     * Add data to upload in Big Query
     *
     * @param RowInterface $data
     * @return UnitOfWork
     */
    public function addData(RowInterface $data): UnitOfWork
    {
        $this->data[] = $data;

        return $this;
    }

    /**
     * Returns data based on project id and SQL query
     *
     * @param string $projectId
     * @param string $query
     * @param bool $useLegacySQL
     * @return \Google\Service\Bigquery\GetQueryResultsResponse
     */
    public function requestData(string $projectId, string $query, bool $useLegacySQL = false): \Google\Service\Bigquery\GetQueryResultsResponse
    {
        $queryRequest = new Google_Service_Bigquery_QueryRequest();
        $queryRequest->setDryRun(false);
        // Legacy SQL is not compatible with partitioned table, so we have to be explicit
        $queryRequest->setUseLegacySql($useLegacySQL);
        $queryRequest->setQuery($query);

        $job = $this->bigQueryClient->jobs->query($projectId, $queryRequest);
        $job_id = $job->getJobReference()->getJobId();
        $pageToken = null;
        do {
            $queryResults = $this->bigQueryClient->jobs->getQueryResults($projectId, $job_id);
            $queryResults->setPageToken($pageToken);
        } while (!$queryResults->getJobComplete());

        return $queryResults;
    }

    /**
     * Upload data to Google Big Query using a file stored in Google Cloud Storage.
     * This method will create 1 file in cloud storage per metadata type and 1 job in big query per file.
     */
    public function flush(): void
    {
        $dataByMetadata = [];
        $metadataList = [];

        foreach ($this->data as $datum) {
            // Group data by metadata instance. Each group will be uploaded one at a time, using the table specified in metadata
            // This way we can handle specific schemas per entity
            $metadata = $this->metadataRepository->getMetadataForEntity($datum);
            if (!isset($dataByMetadata[get_class($metadata)])) {
                $dataByMetadata[get_class($metadata)] = [];
            }
            $dataByMetadata[get_class($metadata)][] = $datum;
            $metadataList[get_class($metadata)] = $metadata;
        }

        foreach ($dataByMetadata as $metadataClass => $data) {
            $this->uploadData($metadataList[$metadataClass], $data);
        }

        $this->data = [];
    }

    private function uploadData(MetadataInterface $metadata, array $data): void
    {
        $job = $this->jobFactory->createJob($metadata, $data);
        $this->bigQueryClient->jobs->insert($metadata->getProjectId(), $job);
    }


}
