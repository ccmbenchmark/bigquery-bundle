<?php

namespace CCMBenchmark\BigQueryBundle\DependencyInjection;

use CCMBenchmark\BigQueryBundle\BigQuery\MetadataInterface;

class SchemaValidator
{
    const KEYS = ['name', 'mode', 'type'];

    /**
     * @see {https://cloud.google.com/bigquery/docs/reference/rest/v2/tables?hl=fr#schema.fields.mode}
     */
    const MODES = ['NULLABLE', 'REQUIRED', 'REPEATED'];

    /**
     * @see {https://cloud.google.com/bigquery/docs/reference/rest/v2/tables?hl=fr#schema.fields.type}
     */
    const TYPES = ['STRING', 'BYTES', 'INTEGER', 'INT64', 'FLOAT', 'FLOAT64', 'BOOLEAN', 'BOOL', 'TIMESTAMP', 'DATE', 'TIME', 'DATETIME', 'RECORD', 'STRUCT'];

    private $metadata;

    public function __construct(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    public function validate(): void
    {
        foreach ($this->metadata->getSchema() as $index => $row) {
            $this->validateRow($index, $row);
        }
    }

    private function validateRow(int $index, array $row): bool
    {
        $this->validateKeys($index, array_keys($row));
        $this->validateMode($index, $row['mode']);
        $this->validateType($index, $row['type']);
        $this->validateName($index, $row['name']);

        return true;
    }

    private function validateKeys(int $index, array $keys): void
    {
        foreach ($keys as $key) {
            if (!in_array($key, self::KEYS)) {
                throw new InvalidSchemaException(sprintf(
                    'Invalid key "%s" in the schema of Metadata %s on row %d. Valid keys are: %s',
                    $key,
                    get_class($this->metadata),
                    $index,
                    implode(', ', self::KEYS)
                ));
            }
        }
        if (array_diff(self::KEYS, $keys) !== []) {
            throw new InvalidSchemaException(sprintf(
                'Missing  keys "%s" in the schema of Metadata %s on row %d. Required keys are: %s',
                implode(', ', array_diff(self::KEYS, $keys)),
                get_class($this->metadata),
                $index,
                implode(', ', self::KEYS)
            ));
        }
    }

    private function validateMode(int $index, string $mode): void
    {
        if (!in_array(strtoupper($mode), self::MODES)) {
            throw new InvalidSchemaException(sprintf(
                'Invalid mode "%s" in the schema of Metadata %s on row %d. Valid modes are: %s',
                $mode,
                get_class($this->metadata),
                $index,
                implode(', ', self::MODES)
            ));
        }
    }

    private function validateType(int $index, string $type): void
    {
        if (!in_array(strtoupper($type), self::TYPES)) {
            throw new InvalidSchemaException(sprintf(
                'Invalid type "%s" in the schema of Metadata %s on row %d. Valid types are: %s',
                $type,
                get_class($this->metadata),
                $index,
                implode(', ', self::TYPES)
            ));
        }
    }

    private function validateName(int $index, string $name): void
    {
        if (!preg_match('/^[a-z]([_a-z0-9]+)$/i', $name)) {
            throw new InvalidSchemaException(sprintf(
                'Invalid name "%s" in the schema of Metadata %s on row %d. Valid names can contain letters, numbers and underscores.',
                $name,
                get_class($this->metadata),
                $index
            ));
        }
    }
}
