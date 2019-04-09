<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

abstract class AbstractMetadata implements MetadataInterface
{
    private $defaultProjectId;
    private $defaultDatasetId;

    public function __construct($defaultProjectId, $defaultDatasetId)
    {
        $this->defaultProjectId = $defaultProjectId;
        $this->defaultDatasetId = $defaultDatasetId;
    }

    abstract  public function getEntityClass();

    public function getDatasetId()
    {
        return $this->defaultDatasetId;
    }

    public function getProjectId()
    {
        return $this->defaultProjectId;
    }

    abstract  public function getTableId();
}
