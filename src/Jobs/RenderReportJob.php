<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Builder\ChunkIncrementalResults;
use MBLSolutions\Report\Support\Enums\JobStatus;
use Throwable;

abstract class RenderReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Report $report;

    public ReportJob $reportJob;

    /** @var null|mixed */
    public $authenticatable;

    public ?ScheduledReport $schedule = null;

    public array $request;

    public int $limit = 50000;

    /** Run once; a retry would start a second copy of the same export */
    public $tries = 1;

    /**
     * Get the Service
     *
     * @return BuildReportService
     */
    protected function getBuildReportService(): BuildReportService
    {
        return new BuildReportService($this->report, $this->request, false, $this->authenticatable);
    }

    /**
     * Get the Chunk Incremental Results Helper
     *
     * @return ChunkIncrementalResults
     */
    public function getChunkIncrementalResultsHelper(): ChunkIncrementalResults
    {
        return new ChunkIncrementalResults($this->getBuildReportService()->buildReportQuery(), $this->limit);
    }

    /**
     * The worker stopped the job (e.g. it timed out): mark it failed
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $this->reportJob->update([
            'status' => JobStatus::FAILED,
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Handle/Log Exception
     *
     * @param Exception $exception
     * @param string|null $query
     * @throws Exception
     */
    protected function handleJobException(Exception $exception, ?string $query = null): void
    {
        $data = [
            'status' => JobStatus::FAILED,
            'exception' => $exception->getMessage(),
        ];

        if ($query) {
            $data['query'] = $query;
        }

        $this->reportJob->update($data);

        throw $exception;
    }

}