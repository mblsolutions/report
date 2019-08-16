<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Maps\ReportResultMap;

abstract class ReportExport
{
    /** @var array $protectedKeys */
    protected CONST PROTECTED_KEYS = ['signature', 'driver', 'expires', 'format', 'uid'];

    /**
     * Get Report Builder
     *
     * @param $report
     * @param $params
     * @return BuildReportService
     */
    protected function getReportBuilder($report, $params): BuildReportService
    {
        return new BuildReportService($report, $params);
    }

    /**
     * Format Results
     *
     * @param BuildReportService $service
     * @param Collection $collection
     * @return Collection
     */
    protected function formatResults(BuildReportService $service, Collection $collection): Collection
    {
        return $collection->transform(static function ($attributes) use ($service) {
            $map = new ReportResultMap($attributes);

            return $map->format($service->selects());
        });
    }

}