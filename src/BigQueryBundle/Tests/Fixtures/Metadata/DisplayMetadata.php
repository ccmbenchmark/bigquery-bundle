<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Fixtures\Metadata;

use CCMBenchmark\BigQueryBundle\BigQuery\AbstractMetadata;
use CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity\Display;

class DisplayMetadata extends AbstractMetadata
{
    public function getEntityClass()
    {
        return Display::class;
    }

    public function getTableId()
    {
        return 'display';
    }

    public function getSchema()
    {
        return [
            ["mode" => "NULLABLE", "name" => "viewViewedImpressions", "type" => "INTEGER"],
            ["mode" => "NULLABLE", "name" => "partner", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "date", "type" => "DATE"],
            ["mode" => "NULLABLE", "name" => "country", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "viewMeasuredImpressions", "type" => "INTEGER"],
            ["mode" => "NULLABLE", "name" => "net_revenue", "type" => "FLOAT"],
            ["mode" => "NULLABLE", "name" => "device", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "format", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "requests", "type" => "INTEGER"],
            ["mode" => "NULLABLE", "name" => "clics", "type" => "INTEGER"],
            ["mode" => "NULLABLE", "name" => "impressions", "type" => "INTEGER"],
            ["mode" => "NULLABLE", "name" => "type", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "site", "type" => "STRING"],
            ["mode" => "NULLABLE", "name" => "created_at", "type" => "TIMESTAMP"]
        ];
    }
}
