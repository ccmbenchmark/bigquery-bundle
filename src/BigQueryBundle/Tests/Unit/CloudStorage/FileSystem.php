<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Unit\CloudStorage;


use atoum;

class FileSystem extends atoum
{
    /**
     * @var \Google_Service_Storage
     */
    private $googleStorageMock;

    /**
     * @var \CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem
     */
    private $instance;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator()->orphanize('__construct');
        $googleClientMock = new \mock\Google\Client();

        $this->googleStorageMock = $this->newMockInstance(\Google\Service\Storage::class, null, null, [
            $googleClientMock
        ]);
        $this->googleStorageMock->objects = new class{
            public $insertions = [];
            public $deletions = [];
            public function insert($bucket, \Google_Service_Storage_StorageObject $object, $metadata) {
                $this->insertions[] = ['bucket' => $bucket, 'object' => $object, 'metadata' => $metadata];
                return true;
            }
            public function delete($bucket, $name) {
                $this->deletions[] = ['bucket' => $bucket, 'name' => $name];
            }
        };
        $this->instance = new \CCMBenchmark\BigQueryBundle\CloudStorage\FileSystem($this->googleStorageMock);
    }

    public function test Store Should Insert An Object Into Storage()
    {
        $this
            ->if($this->instance->store('bucket_test', 'myobject', 'application/json', 'TEST'))
            ->then($dataInsertion = $this->googleStorageMock->objects->insertions[0])
                ->object($dataInsertion['object'])
                    ->isInstanceOf(\Google_Service_Storage_StorageObject::class)
                ->string($dataInsertion['object']->getName())
                    ->isEqualTo('myobject')
                ->string($dataInsertion['object']->getContentType())
                    ->isEqualTo('application/json')
                ->string($dataInsertion['bucket'])
                    ->isEqualTo('bucket_test')
                ->string($dataInsertion['metadata']['mimeType'])
                    ->isEqualTo('application/json')
                ->string($dataInsertion['metadata']['data'])
                    ->isEqualTo('TEST')
                ->string($dataInsertion['metadata']['uploadType'])
                    ->isEqualTo('media')
        ;
    }

    public function test Delete Should Remove An Object From Storage()
    {
        $this
            ->if($this->instance->delete('bucket_test', 'myobject'))
            ->then($dataDeletion = $this->googleStorageMock->objects->deletions[0])
                ->string($dataDeletion['bucket'])
                    ->isEqualTo('bucket_test')
                ->string($dataDeletion['name'])
                    ->isEqualTo('myobject')
        ;
    }
}
