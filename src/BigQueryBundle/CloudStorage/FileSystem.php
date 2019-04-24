<?php

namespace CCMBenchmark\BigQueryBundle\CloudStorage;

class FileSystem implements FileSystemInterface
{
    private $storageClient;

    public function __construct(\Google_Service_Storage $storageClient)
    {
        $this->storageClient = $storageClient;
    }

    public function store($bucket, $name, $mime, $data): void
    {
        $object = new \Google_Service_Storage_StorageObject();
        $object->setContentType($mime);
        $object->setName($name);

        $this->storageClient->objects->insert($bucket, $object,
            [
                'uploadType' => 'media',
                'data' => $data,
                'mimeType' => $mime
            ]
        );
    }

    public function delete($bucket, $name): void
    {
        $this->storageClient->objects->delete($bucket, $name);
    }
}
