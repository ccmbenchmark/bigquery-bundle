<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery\Entity;

interface RowInterface extends \JsonSerializable
{
    public function getCreatedAt();
}
