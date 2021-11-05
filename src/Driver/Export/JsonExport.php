<?php

namespace MBLSolutions\Report\Driver\Export;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;

class JsonExport extends ReportExport
{
    /** @var string $name */
    public $name = 'JSON File (.json)';

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

        return response()->streamDownload(function () use ($service, $report, $params) {
            $this->buildJsonExport($service, $report, $params, (bool) $params['format']);
        }, $report->name . '_' . time() . '.json');
    }

    /**
     * Build CSV Export
     *
     * @param BuildReportService $service
     * @param Report $report
     * @param array $params
     * @param bool $format
     * @return self
     * @codeCoverageIgnore
     */
    protected function buildJsonExport(BuildReportService $service, Report $report, array $params = [], bool $format = false): self
    {
        print '[';

        $this->getMeta($service, $report, $params);

        $this->getData($service, $format);

        print ']';

        return $this;
    }

    /**
     * Get Report Meta Data
     *
     * @param BuildReportService $service
     * @param Report $report
     * @param array $params
     * @return self
     * @codeCoverageIgnore
     */
    public function getMeta(BuildReportService $service, Report $report, array $params = []): self
    {
        print collect([
                'report' => [
                    'name' => $report->name,
                    'description' => $report->description,
                    'date' => Carbon::now()->toIso8601String()
                ],
                'headings' => $service->headings(),
                'params' => collect($params)->except(self::PROTECTED_KEYS),
            ])->toJson() . ',';

        return $this;
    }

    /**
     * Get Report Data
     *
     * @param $service
     * @param bool $format
     * @return self
     * @codeCoverageIgnore
     */
    public function getData(BuildReportService $service, bool $format): self
    {
        $service->query()->orderBy($service->headings()->first())->chunk(100, function (Collection $chunk) use ($service, $format) {
            if ($format) {
                $chunk = $this->formatResults($service, $chunk);
            }

            print $chunk->toJson() . ',';
        });

        return $this;
    }
    
}