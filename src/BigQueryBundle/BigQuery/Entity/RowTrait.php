<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery\Entity;

trait RowTrait
{
    private $createdAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
