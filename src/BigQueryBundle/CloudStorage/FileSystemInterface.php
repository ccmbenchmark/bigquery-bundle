<?php

namespace CCMBenchmark\BigQueryBundle\CloudStorage;

interface FileSystemInterface
{
    public function store($bucket, $name, $mime, $data):void;

    public function delete($bucket, $name):void;
}
