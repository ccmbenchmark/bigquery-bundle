<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

use CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator;

interface MetadataInterface
{
    /**
     * @return string The name of the class entity coupled to this metadata
     */
    public function getEntityClass(): string;

    /**
     * @return string The dataset where data will be stored in bigquery
     */
    public function getDatasetId(): string;

    /**
     * @return string The project id in bigquery
     */
    public function getProjectId(): string;

    /**
     * @return string The table name in bigquery
     */
    public function getTableId(): string;

    /**
     * @return array The schema.
     * It's an array representation of a valid schema in google big query.
     * @see{https://cloud.google.com/bigquery/docs/reference/rest/v2/tables?hl=fr#schema}
     * @see SchemaValidator
     */
    public function getSchema(): array;
}
