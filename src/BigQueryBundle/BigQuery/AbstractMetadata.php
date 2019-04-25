<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

abstract class AbstractMetadata implements MetadataInterface
{
    private $defaultProjectId;
    private $defaultDatasetId;

    /**
     * AbstractMetadata constructor.
     * @param string $defaultProjectId Id of your project on GCP
     * @param string $defaultDatasetId Dataset in Big Query for this table
     */
    public function __construct(string $defaultProjectId, string $defaultDatasetId)
    {
        $this->defaultProjectId = $defaultProjectId;
        $this->defaultDatasetId = $defaultDatasetId;
    }

    /**
     * @return string The name of the entity related to this metadata
     */
    abstract  public function getEntityClass(): string;

    /**
     * @return string Dataset id in Big Query for this table
     */
    public function getDatasetId(): string
    {
        return $this->defaultDatasetId;
    }

    /**
     * @return string Id of your project on GCP
     */
    public function getProjectId(): string
    {
        return $this->defaultProjectId;
    }

    /**
     * @return string Table id in Big Query
     */
    abstract  public function getTableId(): string;
}
