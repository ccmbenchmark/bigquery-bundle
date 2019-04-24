<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

interface MetadataInterface
{
    /**
     * @return string The name of the class entity coupled to this metadata
     */
    public function getEntityClass();

    /**
     * @return string The dataset where data will be stored in bigquery
     */
    public function getDatasetId();

    /**
     * @return string The project id in bigquery
     */
    public function getProjectId();

    /**
     * @return string The table name in bigquery
     */
    public function getTableId();

    /**
     * @return array The schema
     */
    public function getSchema();
}
