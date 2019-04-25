<?php

namespace CCMBenchmark\BigQueryBundle\CloudStorage;

interface FileSystemInterface
{
    public function store(string $bucket, string $name, string $mime, string $data):void;

    public function delete(string $bucket, string $name):void;
}
