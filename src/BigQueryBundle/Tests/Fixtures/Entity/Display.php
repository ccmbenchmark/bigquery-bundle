<?php

namespace CCMBenchmark\BigQueryBundle\Tests\Fixtures\Entity;

use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowInterface;
use CCMBenchmark\BigQueryBundle\BigQuery\Entity\RowTrait;

class Display implements RowInterface
{
    use RowTrait;

    private $reportDate;
    private $partnerName;
    private $rowType;
    private $country;
    private $site;
    private $device;
    private $format;
    private $requests;
    private $clicks;
    private $netRevenue;
    private $impressions;
    private $viewMeasuredImpressions;
    private $viewViewedImpressions;
    private $videoCompleted;

    public function __construct(
        \DateTimeInterface $reportDate,
        $partnerName,
        $rowType,
        $country,
        $site,
        $device,
        $format,
        $requests,
        $netRevenue,
        $impressions,
        \DateTimeInterface $createdAt,
        $clicks = null,
        $viewMeasuredImpressions = null,
        $viewViewedImpressions = null
    )
    {
        $this->reportDate = $reportDate;
        $this->partnerName = $partnerName;
        $this->rowType = $rowType;
        $this->country = $country;
        $this->site = $site;
        $this->device = $device;
        $this->format = $format;
        $this->requests = (int)$requests;
        if ($clicks !== null) {
            $this->clicks = (int)$clicks;
        }
        $this->netRevenue = (float)$netRevenue;
        $this->impressions = (int)$impressions;
        $this->createdAt = $createdAt;
        if ($viewMeasuredImpressions !== null) {
            $this->viewMeasuredImpressions = (int)$viewMeasuredImpressions;
        }
        if ($viewViewedImpressions !== null) {
            $this->viewViewedImpressions = (int)$viewViewedImpressions;
        }
    }


    public function jsonSerialize()
    {
        return [
            'date' => $this->reportDate->format('Y-m-d'),
            'country' => $this->country,
            'site' => $this->site,
            'device' => $this->device,
            'format' => $this->format,
            'requests' => $this->requests,
            'clics' => $this->clicks,
            'net_revenue' => $this->netRevenue,
            'impressions' => $this->impressions,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'partner' => $this->partnerName,
            'type' => $this->rowType,
            'viewMeasuredImpressions' => $this->viewMeasuredImpressions,
            'viewViewedImpressions' => $this->viewViewedImpressions,
        ];
    }
}
