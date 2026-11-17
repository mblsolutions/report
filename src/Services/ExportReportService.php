<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportExported;
use MBLSolutions\Report\Exceptions\UnknownExportDriverException;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;

class ExportReportService
{
    protected Report $report;

    protected Request $request;

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
        $driver = $this->getDriver();

        $rows = $this->countRows();

        $result = $driver->export($this->report, $this->request->toArray());

        Event::dispatch(new ReportExported(
            $this->report,
            Auth::id(),
            (new Collection($this->request->toArray()))->except(['signature', 'expires', 'uid', 'driver'])->all(),
            $rows
        ));

        return $result;
    }

    /**
     * Count the rows up to the cap. Direct exports over the cap are refused.
     *
     * @return int
     */
    protected function countRows(): int
    {
        $cap = (int) config('report.max_rows.export', 50000);

        $query = (new BuildReportService($this->report, $this->request->toArray()))->buildReportQuery();

        // Count result rows (keeps the select list, so an aggregate is one row), up to cap + 1
        $rows = $query->getConnection()->query()
            ->fromSub($query->cloneWithout(['orders', 'limit', 'offset'])->cloneWithoutBindings(['order'])->limit($cap + 1), 'report_rows')
            ->count();

        abort_if(
            $rows > $cap,
            422,
            sprintf('This report has more than %s rows. Use the queued export.', number_format($cap))
        );

        return $rows;
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