<?php

namespace MBLSolutions\Report\Tests\Fakes\ExportDriver;

use Illuminate\Http\Response;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Models\Report;

class FakeExportDriver implements ExportDriver
{
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

}