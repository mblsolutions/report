<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Enums\JobStatus;

class RenderReport extends RenderReportJob
{

    public function __construct(string $uuid, Report $report, array $request = [], $authenticatable = null, string $schedule = null)
    {
        $this->report = $report;
        $this->request = $request;
        $this->authenticatable = $authenticatable;
        $this->schedule = $schedule;
        $this->chunkLimit = config('report.chunk_limit', 50000);

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
            $data = [
                'status' => JobStatus::RUNNING,
                'processed' => 0,
                'total' => $this->getBuildReportService()->getTotalResults(),
                'parameters' => $this->request,
                'formatted_parameters' => (new BuildReportService($this->report, $this->request))->getFormattedParameters($this->request),
                'schedule_id' => $this->schedule,
            ];

            if ($this->authenticatable) {
                $data['authenticatable_id'] = $this->authenticatable;
            }

            $this->reportJob->update($data);

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

        Event::dispatch(new ReportRenderStarted($this->report, $job = $model->refresh()));

        return $job;
    }



}