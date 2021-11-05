<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;

class PrintExport extends ReportExport
{
    /** @var string $name */
    public $name = 'Print Report';

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

        $format = $params['format'] ?? false;

        return view('mbl-report::print', [
            'report' => $report,
            'params' => $this->getParameters($params),
            'user' => auth()->user(),
            'date' => Carbon::now()->toIso8601String(),
            'headings' => $service->headings(),
            'data' => $this->getReportData($service, (bool) $format)
        ]);
    }

    /**
     * Get Report Data
     *
     * @param BuildReportService $service
     * @param bool $format
     * @return Collection
     * @codeCoverageIgnore
     */
    public function getReportData(BuildReportService $service, bool $format = false): Collection
    {
        $collection = $service->query()->get();

        if ($format) {
            $this->formatResults($service, $collection);
        }

        return $collection;
    }

    /**
     * Get Report Parameters
     *
     * @param array $params
     * @return array
     * @codeCoverageIgnore
     */
    protected function getParameters(array $params = []): array
    {
        $params = collect($params);
        $formatted = [];

        foreach ($params->except(self::PROTECTED_KEYS)->toArray() as $key => $param) {
            $formatted[$this->formatParameterKey($key)] = $this->formatParameterValue($param);
        }

        return $formatted;
    }

    /**
     * Format Parameter Key
     *
     * @param string $key
     * @return string
     * @codeCoverageIgnore
     */
    private function formatParameterKey(string $key): string
    {
        $parts = explode('_', $key);

        return implode(' ', array_map('ucwords', $parts));
    }

    /**
     * Format Parameter Value
     *
     * @param string $param
     * @return string
     * @codeCoverageIgnore
     */
    private function formatParameterValue(string $param): string
    {
        return ucwords($param);
    }

}