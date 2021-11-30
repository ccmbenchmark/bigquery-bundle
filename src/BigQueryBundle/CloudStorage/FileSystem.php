<?php

namespace CCMBenchmark\BigQueryBundle\CloudStorage;

class FileSystem implements FileSystemInterface
{
    private $storageClient;

    public function __construct(\Google\Service\Storage $storageClient)
    {
        $this->storageClient = $storageClient;
    }

    /**
     * @param $bucket
     * @param $name
     * @param $mime
     * @param $data
     */
    public function store(string $bucket, string $name, string $mime, string $data): void
    {
        $object = new \Google\Service\Storage\StorageObject();
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

    public function delete(string $bucket, string $name): void
    {
        $this->storageClient->objects->delete($bucket, $name);
    }
}
