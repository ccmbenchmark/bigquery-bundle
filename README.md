# BigQuery Bundle

This bundle offers a simple method to batch upload data to Google bigquery.


## Concept

3 concepts are useful to work with this bundle:

### Entities
An entity is any object implementing `\CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface`.
This interface extends JsonSerializable to handle the export to bigquery.
An entity is coupled to a metadata object.

### Metadata
Metadata are responsible to store the schema related to your entity and other information used to store data into bigquery.
A metadata is a class implementing `\CCMBenchmark\BigQueryBundle\BigQuery\MetadataInterface`.

### UnitOfWork
The UnitOfWork is provided by the bundle.
It's responsible to store the data and then to upload it to bigquery.

### Full example

```php
<?php
class MyMetadata implements CCMBenchmark\BigQueryBundle\BigQuery\MetadataInterface {
    public function getEntityClass(): string {
        return MyEntity::class;
    }

    public function getDatasetId(): string {
        return 'mydataset';
    }
    public function getProjectId(): string {
        return 'myproject';
    }
    public function getTableId(): string {
        return 'mytable';
    }
    public function getSchema(): array {
        return [
            [ "mode"=> "NULLABLE", "name"=> "sessions", "type"=> "INTEGER" ]
        ];
    }
}

class MyEntity implements CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface {
    use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowTrait;

    private $sessions;
    public function __construct(int $sessions) {
        $this->sessions = $sessions;
    }

    public function jsonSerialize()
    {
        return [
            'sessions' => $this->sessions,
            'created_at' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
    }
}

// @var $unitOfWork \CCMBenchmark\BigQueryBundle\BigQuery\UnitOfWork
$unitOfWork->addData(
    new MyEntity(
       1000
    )
);

// Your data will be uploaded when calling this method
$unitOfWork->flush();
```

## Getting started

### Install this package

1. Require the package `composer require ccmbenchmark/bigquery-bundle`
2. Add it to your kernel:

**Symfony 4+**:

```php
<?php
//config/bundles.php
return [
    (..)
    \CCMBenchmark\BigQueryBundle\BigQueryBundle::class => ['all' => true]
]
```

**Symfony 3.4**:

```php
<?php
//app/AppKernel.php
$bundles = array(
    (...)
    new \CCMBenchmark\BigQueryBundle\BigQueryBundle(),
);
```

### Setup your project on google cloud storage and google bigquery

To upload data to google big query using google cloud storage, you need:

1. A valid [google cloud project](https://console.cloud.google.com/) with the following apis: BigQuery API, Cloud Storage
2. A valid [service account](https://cloud.google.com/iam/docs/creating-managing-service-accounts) with the json api identifier
3. To setup the billing on your account

**Note:** Usage of google cloud storage and google big query can be charged by Google.
So using this bundle can produce charges on your account. You are responsible of that.


### Setup the bundle

```yml
#config/packages/big_query.yml
big_query:
    cloudstorage:
        bucket: [Name of your bucket in google cloud storage]
    api:
        application_name: "My application"
        credentials_file: "[Path to your credentials in json format]"
    proxy: ## Remove this section if you don't have any proxy or set the values to "~"
        host: "%proxy.host%"
        port: "%proxy.port"
```

### Create and declare your metadata
To create a metadata, create a new class implementing MetadataInterface.

To automatically register your metadata into the UnitOfWork, this bundle provides a tag to declare on this service.

```xml
//config/services/services.xml
<service id="AppBundle\MyMetadata">
    <tag name="big_query.metadata" />
</service>
```

At this point your metadata are declared into the UnitOfWork, thanks to a CompilerPass.
You are ready to upload data.

### Working with UnitOfWork service
You need to use the service `CCMBenchmark\BigQueryBundle\BigQuery\UnitOfWork`.
This service offers a simple API.

#### Upload data to google bigquery
Call `addData` to store a new Entity to upload.
When all you're entities are in the UnitOfWork, call `flush` to upload it.

#### Request data from google bigquery
Call `requestData` to make a request to the specified `projectId` it will return a `\Google\Service\Bigquery\GetQueryResultsResponse`
```php
<?php
class myDataSource implements DataSourceInterface
{
    private string $projectId;
    private UnitOfWork $unitOfWork;
    
    public function __construct(UnitOfWork $unitOfWork, string $projectId)
    {
        $this->unitOfWork = $unitOfWork;
        $this->projectId = $projectId;
    }

    public function getData(\DateTimeImmutable $reportDate, array $sites): array
    {
    $queryResults = $this->unitOfWork->queryData($this->projectId, 'SELECT field1, field2, field3 FROM myDataset.myTable');

    $data = [];
    foreach ($queryResults->getRows() as $row) {
        $data[] = $row->current()->getV();
    }
}
```

## Debugging
If there is no exception thrown by the code but you cannot find your data in bigquery, you should follow this steps:

1. Check [google cloud storage](https://console.cloud.google.com/storage/browser).
A file named "reporting-[YYYY-mm-dd]-[uniqId].json" should be here. If there is no such file, check permissions on GCP.
2. Check [google big query](https://console.cloud.google.com/bigquery).
A new job should have been submitted. Be sure to check in the project history. If the job is errored, try to submit it again using the UI, bigquery will display the errors.

## Cleaning google cloud storage
This is out of scope of this bundle, but to save storage you can [define a lifecycle](https://cloud.google.com/storage/docs/lifecycle) in your bucket.
