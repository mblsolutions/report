<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\SpreadsheetCell;

class CsvExport extends ReportExport
{
    /** @var string $name */
    public string $name = 'CSV File (.csv)';

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
            $this->buildCsvExport($service, (bool) ($params['format'] ?? false));
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
        $this->printCsvLine($service->headings()->all());

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

            $chunk->each(function ($row) {
                $this->printCsvLine(array_map([SpreadsheetCell::class, 'guard'], (array) $row));
            });
        });

        return $this;
    }

    /**
     * Print one quoted CSV line
     *
     * @param array $cells
     * @return void
     * @codeCoverageIgnore
     */
    protected function printCsvLine(array $cells): void
    {
        $output = fopen('php://output', 'wb');

        fputcsv($output, $cells, ',', '"', '');

        fclose($output);
    }

}