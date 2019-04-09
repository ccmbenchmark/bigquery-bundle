<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery\Entity;

trait RowTrait
{
    private $createdAt;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
