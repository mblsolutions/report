<?php

namespace MBLSolutions\Report\Tests\Fakes\ExportDriver;

use Illuminate\Http\Response;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Models\Report;

class FakeExportDriver implements ExportDriver
{
    public string $name = 'Fake Export Driver';

    /**
     * Export report result data
     *
     * @param Report $report
     * @param array $params
     * @return mixed
     */
    public function export(Report $report, array $params = [])
    {
        return new Response([
            'report' => $report->toArray(),
            'params' => $params
        ]);
    }

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

}