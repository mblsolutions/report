<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Maps\ReportResultMap;

abstract class ReportExport implements ExportDriver
{
    /** @var array $protectedKeys */
    protected CONST PROTECTED_KEYS = ['signature', 'driver', 'expires', 'format', 'uid'];

    /**
     * Get Export Driver Name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->name) {
            return $this->name;
        }

        return null;
    }

    /**
     * Get Report Builder
     *
     * @param $report
     * @param $params
     * @return BuildReportService
     */
    protected function getReportBuilder($report, $params): BuildReportService
    {
        $service = new BuildReportService($report, $params);

        $service->buildReportQuery();

        return $service;
    }

    /**
     * Format Results
     *
     * @param BuildReportService $service
     * @param Collection $collection
     * @return Collection
     * @codeCoverageIgnore
     */
    protected function formatResults(BuildReportService $service, Collection $collection): Collection
    {
        return $collection->transform(static function ($attributes) use ($service) {
            $map = new ReportResultMap($attributes);

            return $map->format($service->selects());
        });
    }

}