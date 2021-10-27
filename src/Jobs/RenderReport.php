<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Enums\JobStatus;

class RenderReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Report $report;

    public ReportJob $reportJob;

    public function __construct(string $uuid, Report $report)
    {
        $this->report = $report;
        $this->reportJob = $this->initiateRenderReportJob($uuid);
    }

    public function handle(): void
    {
        try {
            $this->reportJob->update(['status' => JobStatus::RUNNING]);

            $service = new BuildReportService($this->report, []); // TODO $request->toArray())

            // TODO maybe store in file? Then read results from file?
            Cache::remember($this->reportJob->getKey(), 86400, function () use ($service) {
                return $service->render();
            });

            $this->reportJob->update(['status' => JobStatus::COMPLETE]);

        } catch (Exception $exception) {
            $this->reportJob->update(['status' => JobStatus::FAILED]);
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