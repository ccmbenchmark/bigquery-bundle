<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata;

use CCMBenchmark\BigQueryBundle\BigQuery\AbstractMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Analytics;

class AnalyticsMetadata extends AbstractMetadata
{
    public function getEntityClass(): string
    {
        return Analytics::class;
    }

    public function getTableId(): string
    {
        return 'analytics';
    }
    
    public function getSchema(): array
    {
        return [
            ["mode"=> "NULLABLE", "name"=> "created_at", "type"=> "TIMESTAMP"],
            ["mode"=> "NULLABLE", "name"=> "sessions", "type"=> "INTEGER"],
            ["mode"=> "NULLABLE", "name"=> "pageviews", "type"=> "INTEGER"],
            ["mode"=> "NULLABLE", "name"=> "device", "type"=> "STRING"],
            ["mode"=> "NULLABLE", "name"=> "site", "type"=> "STRING"],
            ["mode"=> "NULLABLE", "name"=> "country", "type"=> "STRING"],
            ["mode"=> "NULLABLE", "name"=> "date", "type"=> "DATE"]
        ];
    }
}
