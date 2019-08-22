<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;

class CsvExport extends ReportExport implements ExportDriver
{
    /** @var string $name */
    public $name = 'CSV File (.csv)';

    /**
     * Export report result data
     *
     * @param Report $report
     * @param array $params
     * @return mixed
     */
    public function export(Report $report, array $params = [])
    {
        $service = $this->getReportBuilder($report, $params);

        return response()->streamDownload(function () use ($service, $params) {
            $this->buildCsvExport($service, (bool) $params['format']);
        }, $report->name . '_' . time() . '.csv');
    }

    /**
     * Build CSV Export
     *
     * @param BuildReportService $service
     * @param bool $format
     * @return self
     * @codeCoverageIgnore
     */
    protected function buildCsvExport(BuildReportService $service, bool $format): self
    {
        $this->getHeadings($service);

        $this->getRows($service, $format);

        return $this;
    }

    /**
     * Get Column Headings
     *
     * @param $service
     * @return self
     * @codeCoverageIgnore
     */
    protected function getHeadings(BuildReportService $service): self
    {
        print $service->headings()->implode(',') . PHP_EOL;

        return $this;
    }

    /**
     * Get Data Rows
     *
     * @param BuildReportService $service
     * @param bool $format
     * @return self
     * @codeCoverageIgnore
     */
    protected function getRows(BuildReportService $service, bool $format = false): self
    {
        $service->query()->orderBy($service->headings()->first())->chunk(100, function (Collection $chunk) use ($service, $format) {
            if ($format) {
                $chunk = $this->formatResults($service, $chunk);
            }

            $chunk->each(static function ($row) {
                print collect($row)->implode(',') . PHP_EOL;
            });
        });

        return $this;
    }

}