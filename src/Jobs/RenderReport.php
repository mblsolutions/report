<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;

class RenderReport extends RenderReportJob
{

    public function __construct(string $uuid, Report $report, array $request = [])
    {
        $this->report = $report;
        $this->request = $request;

        $this->reportJob = $this->initiateRenderReportJob($uuid);
    }

    /**
     * Execute the Job
     *
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            $this->reportJob->update([
                'status' => JobStatus::RUNNING,
                'processed' => 0,
                'total' => $this->getBuildReportService()->getTotalResults()
            ]);

            ProcessReportExportChunk::dispatch($this->report, $this->reportJob, $this->request, 1);

        } catch (Exception $exception) {
            $this->handleJobException($exception);
        }
    }

    /**
     * Create a ReportJob record
     *
     * @param string $uuid
     * @return ReportJob
     */
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