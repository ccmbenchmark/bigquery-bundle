<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery\Entity;

interface RowInterface extends \JsonSerializable
{
    /**
     * @return \DateTimeInterface the date this line is added in bigquery
     */
    public function getCreatedAt(): \DateTimeInterface;
}
