<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\DependencyInjection;

use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;
use CCMBenchmark\BigQueryBundle\BigQuery\MetadataInterface;
use CCMBenchmark\BigQueryBundle\DependencyInjection\InvalidSchemaException;
use mageekguy\atoum;

class SchemaValidator  extends atoum
{
    /**
     * @var RowInterface
     */
    private $entity;

    public function beforeTestMethod($method)
    {
        $this->entity = new class implements RowInterface {
            public function getCreatedAt(): \DateTimeInterface
            {
                return new \DateTime();
            }

            public function jsonSerialize()
            {
                return [];
            }
        };
    }

    private function generateMetadata(array $schema)
    {
        return new class ($this->entity, $schema) implements MetadataInterface {
            /**
             * @var RowInterface
             */
            private $entity;

            private $schema;
            public function __construct(RowInterface $entity, array $schema)
            {
                $this->entity = $entity;
                $this->schema = $schema;
            }

            public function getEntityClass(): string
            {
                return get_class($this->entity);
            }

            public function getDatasetId(): string
            {
                return 'dataset';
            }

            public function getProjectId(): string
            {
                return 'project';
            }

            public function getTableId(): string
            {
                return 'table';
            }

            public function getSchema(): array
            {
                return $this->schema;
            }
        };
    }

    public function test Validate Should Be Void On Valid Schema()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NULLABLE', 'name' => 'TEST', 'type' => 'INTEGER'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->variable($schemaValidator->validate())
                    ->isNull()
        ;
    }

    public function test Validate Should Throw Exception When A Key Is Missing()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NULLABLE', 'name' => 'TEST'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->exception(function() use ($schemaValidator) {
                    $schemaValidator->validate();
                })
                    ->isInstanceOf(InvalidSchemaException::class)
                        ->string($this->exception->getMessage())
                            ->matches('/Missing  keys "type" in the schema of Metadata class@anonymous.* on row 0\. Required keys are: name, mode, type/')
        ;
    }

    public function test Validate Should Throw Exception When A Key Is Added()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NULLABLE', 'name' => 'TEST', 'type' => 'INTEGER', 'keyAdded' => 'test'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->exception(function() use ($schemaValidator) {
                    $schemaValidator->validate();
                })
                    ->isInstanceOf(InvalidSchemaException::class)
                        ->string($this->exception->getMessage())
                            ->matches('/Invalid key "keyAdded" in the schema of Metadata class@anonymous.* on row 0\. Valid keys are: name, mode, type/')
        ;
    }

    public function test Validate Should Throw Exception When The Mode Is Invalid()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NOTVALID', 'name' => 'TEST', 'type' => 'INTEGER'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->exception(function() use ($schemaValidator) {
                    $schemaValidator->validate();
                })
                    ->isInstanceOf(InvalidSchemaException::class)
                        ->string($this->exception->getMessage())
                            ->matches('/Invalid mode "NOTVALID" in the schema of Metadata class@anonymous.* on row 0\. Valid modes are: .*/')
        ;
    }

    public function test Validate Should Throw Exception When The Type Is Invalid()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NULLABLE', 'name' => 'TEST', 'type' => 'NOTVALID'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->exception(function() use ($schemaValidator) {
                    $schemaValidator->validate();
                })
                    ->isInstanceOf(InvalidSchemaException::class)
                        ->string($this->exception->getMessage())
                            ->matches('/Invalid type "NOTVALID" in the schema of Metadata class@anonymous.* on row 0\. Valid types are: .*/')
        ;
    }

    public function test Validate Should Throw Exception When The Name Is Invalid()
    {
        $schema = $this->generateMetadata([
            [
                'mode' => 'NULLABLE', 'name' => 'Not A Valid Name', 'type' => 'INTEGER'
            ]
        ]);

        $this
            ->if($schemaValidator = new \CCMBenchmark\BigQueryBundle\DependencyInjection\SchemaValidator($schema))
                ->exception(function() use ($schemaValidator) {
                    $schemaValidator->validate();
                })
                    ->isInstanceOf(InvalidSchemaException::class)
                        ->string($this->exception->getMessage())
                            ->matches('/Invalid name "Not A Valid Name" in the schema of Metadata class@anonymous.* on row 0\. Valid names can contain letters, numbers and underscores\./')
        ;
    }
}
