<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

class RenderReport extends RenderReportJob
{

    public function __construct(string $uuid, Report $report, array $request = [], $authenticatable = null, ScheduledReport $schedule = null)
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
            if ($this->schedule) {
                $this->updateRequestForSchedule($this->report->fields);
            }

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

    /**
     * Update date/time request fields for schedule
     *
     * @param Collection $fields
     * @return void
     */
    protected function updateRequestForSchedule(Collection $fields): void
    {
        foreach ($this->request as $key => $value) {
            $this->replaceRequestValue($key, $fields);
        }
    }

    /**
     * Replace a request value
     *
     * @param string $key
     * @param Collection $fields
     */
    protected function replaceRequestValue(string $key, Collection $fields): void
    {
        $field = $fields->firstWhere('alias', $key);
        $date = Carbon::now();

        if ($field === null) {
            return;
        }

        if (in_array($key, config('report.scheduled_date_start'), true)) {
            if ($field->getAttribute('type') === ReportFieldType::DATE) {
                $this->request[$key] = $this->handleStartDate($date)->format('Y-m-d');
            } elseif ($field->getAttribute('type') === ReportFieldType::DATETIME) {
                $this->request[$key] = $this->handleStartDate($date)->format('Y-m-d 00:00:00');
            } elseif ($field->getAttribute('type') === ReportFieldType::TIME) {
                $this->request[$key] = '00:00';
            }
        }

        if (in_array($key, config('report.scheduled_date_end'), true)) {
            if ($field->getAttribute('type') === ReportFieldType::DATE) {
                $this->request[$key] = $date->subDay()->format('Y-m-d');
            } elseif ($field->getAttribute('type') === ReportFieldType::DATETIME) {
                $this->request[$key] = $date->subDay()->format('Y-m-d 23:59:59');
            } elseif ($field->getAttribute('type') === ReportFieldType::TIME) {
                $this->request[$key] = '23:59:59';
            }
        }
    }

    /**
     * Alter date to the required frequency
     *
     * @param Carbon $date
     * @return Carbon
     */
    protected function handleStartDate(Carbon $date): Carbon
    {
        switch ($this->schedule->getAttribute('frequency')) {
            case ReportSchedule::DAILY:
                return $date->subDay();
            case ReportSchedule::WEEKLY:
                return $date->subWeek();
            case ReportSchedule::MONTHLY:
                return $date->subMonth();
            case ReportSchedule::QUARTERLY:
                return $date->subQuarter();
            case ReportSchedule::YEARLY:
                return $date->subYear();
        }

        return $date;
    }

}