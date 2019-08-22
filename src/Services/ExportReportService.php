<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Http\Request;
use MBLSolutions\Report\Events\ReportExported;
use MBLSolutions\Report\Exceptions\UnknownExportDriverException;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;

class ExportReportService
{
    /** @var Report $report */
    protected $report;

    /** @var Request $request */
    protected $request;

    /**
     * Export Report Service
     *
     * @param Report $report
     * @param Request $request
     */
    public function __construct(Report $report, Request $request)
    {
        $this->report = $report;
        $this->request = $request;
    }

    /**
     * Handle the Report Export
     *
     * @return mixed;
     * @throws UnknownExportDriverException
     */
    public function handle()
    {
        $result = $this->getDriver()->export($this->report, $this->request->toArray());

        event(new ReportExported($this->report));

        return $result;
    }

    /**
     * Get the Driver based on the hash
     *
     * @return ExportDriver
     * @throws UnknownExportDriverException
     */
    public function getDriver(): ExportDriver
    {
        return (new ReportExportDrivers)->findByHash($this->request->get('driver'));
    }

}