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

    public function addData(RowInterface $data)
    {
        $this->data[] = $data;

        return $this;
    }

    public function flush()
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

    private function uploadData(MetadataInterface $metadata, array $data)
    {
        $job = $this->jobFactory->createJob($metadata, $data);
        $this->bigQueryClient->jobs->insert($metadata->getProjectId(), $job);
    }
}
