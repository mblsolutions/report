<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Export\Report\ReportExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Enums\JobStatus;

class RenderReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Report $report;

    public ReportJob $reportJob;

    public array $request;

    public function __construct(string $uuid, Report $report, array $request = [])
    {
        $this->report = $report;
        $this->reportJob = $this->initiateRenderReportJob($uuid);
        $this->request = $request;
    }

    public function handle(): void
    {
        try {
            $this->reportJob->update(['status' => JobStatus::RUNNING]);

            $service = new BuildReportService($this->report, $this->request, false);

            Excel::store(
                new ReportExport($service),
                config('report.filesystem_path', 'reports/') . $this->reportJob->getKey() . '.csv',
                config('report.filesystem')
            );

            $this->reportJob->update(['status' => JobStatus::COMPLETE]);

        } catch (Exception $exception) {
            $this->reportJob->update(['status' => JobStatus::FAILED]);

            throw $exception;
        }
    }

    protected function initiateRenderReportJob(string $uuid): ReportJob
    {
        $model = new ReportJob([
            'uuid' => $uuid,
            'report_id' => $this->report->getKey(),
            'status' => JobStatus::SCHEDULED,
        ]);

        $model->save();

        event(new ReportRenderStarted($this->report, $job = $model->refresh()));

        return $job;
    }

}