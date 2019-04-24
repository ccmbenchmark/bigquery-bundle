<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;

class MetadataRepository
{
    /**
     * @var MetadataInterface[]
     */
    private $metadata = [];

    public function addMetadata(MetadataInterface $metadatum)
    {
        $this->metadata[] = $metadatum;
    }

    public function getMetadataForEntity(RowInterface $reportingRow)
    {
        foreach ($this->metadata as $metadatum) {
            $class = $metadatum->getEntityClass();
            if ($reportingRow instanceof $class) {
                return $metadatum;
            }
        }
        throw new \RuntimeException('Could not found MetadataInterface for row ' . get_class($reportingRow));
    }
}
