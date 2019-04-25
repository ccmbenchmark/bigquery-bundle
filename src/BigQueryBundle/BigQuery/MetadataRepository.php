<?php

namespace CCMBenchmark\BigQueryBundle\BigQuery;

use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;

class MetadataRepository
{
    /**
     * @var MetadataInterface[]
     */
    private $metadata = [];

    public function addMetadata(MetadataInterface $metadatum): void
    {
        $this->metadata[] = $metadatum;
    }

    /**
     * Find metadata related to an entity
     *
     * @param RowInterface $reportingRow
     * @return MetadataInterface
     */
    public function getMetadataForEntity(RowInterface $reportingRow): MetadataInterface
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
