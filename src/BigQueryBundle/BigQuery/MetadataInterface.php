<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

interface MetadataInterface
{
    public function getEntityClass();
    public function getDatasetId();
    public function getProjectId();
    public function getTableId();
    public function getSchema();
}
