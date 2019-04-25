<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Fixtures\CloudStorage;

use CCMBenchmark\BigQueryBundle\CloudStorage\FileSystemInterface;

class DummyFileSystem implements FileSystemInterface
{
    public $bucket;
    public $name;
    public $mime;
    public $data;

    public function store(string $bucket, string $name, string $mime, string $data) :void
    {
        $this->bucket = $bucket;
        $this->name = $name;
        $this->mime = $mime;
        $this->data = $data;

        return;
    }

    public function delete(string $bucket, string $name) :void
    {
        return;
    }

}
