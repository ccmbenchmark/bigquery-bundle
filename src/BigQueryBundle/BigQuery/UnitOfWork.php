<?php


namespace CCMBenchmark\BigQueryBundle\BigQuery;


use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;
use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem;
use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystemInterface;

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
