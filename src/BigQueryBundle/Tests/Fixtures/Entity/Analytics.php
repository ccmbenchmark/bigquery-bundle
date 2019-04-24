<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity;

use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;
use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowTrait;

class Analytics implements RowInterface
{
    use RowTrait;

    private $reportDate;
    private $country;
    private $site;
    private $device;
    private $pageViews;
    private $sessions;

    public function __construct(
        \DateTimeInterface $reportDate,
        $country,
        $site,
        $device,
        $pageViews,
        $sessions,
        \DateTimeInterface $createdAt
    )
    {
        $this->reportDate = $reportDate;
        $this->country = $country;
        $this->site = $site;
        $this->device = $device;
        $this->pageViews = (int)$pageViews;
        $this->sessions = (int)$sessions;
        $this->createdAt = $createdAt;
    }


    public function jsonSerialize()
    {
        return [
            'date' => $this->reportDate->format('Y-m-d'),
            'country' => $this->country,
            'site' => $this->site,
            'device' => $this->device,
            'pageviews' => $this->pageViews,
            'sessions' => $this->sessions,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
