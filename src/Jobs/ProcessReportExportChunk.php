<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Events\ReportChunkComplete;
use MBLSolutions\Report\Events\ReportRenderComplete;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;
use RuntimeException;

class ProcessReportExportChunk extends RenderReportJob
{
    public int $chunk;

    public function __construct(Report $report, ReportJob $reportJob, array $request, int $chunk, $authenticatable = null)
    {
        $this->report = $report;
        $this->reportJob = $reportJob;
        $this->request = $request;
        $this->authenticatable = $authenticatable;

        $this->limit = config('report.chunk_limit', 50000);
        $this->chunk = $chunk;
    }

    /**
     * Execute the Job
     *
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            $total = $this->getChunkIncrementalResultsHelper()->getTotalResultsForChunk($this->chunk * $this->limit);

            if ($this->checkReportHasResults()) {
                $this->reportJob->update([
                    'total' => $this->reportJob->total += $total
                ]);

                $this->startExportProcess();
            } else {
                $this->completeReportExport();
            }

            Event::dispatch(new ReportChunkComplete($this->report, $this->reportJob));

        } catch (Exception $exception) {
            $this->handleJobException(
                $exception,
                $this->getBuildReportService()->getRenderedChunk($this->getOffset(), $this->limit, true)
            );
        }
    }

    /**
     * Start the Export Process
     *
     * @return void
     */
    protected function startExportProcess(): void
    {
        $this->exportResultsToFile();

        $this->updateReportJobStatus();

        if ($this->isLastChunk()) {
            $this->completeReportExport();
        } else {
            $this->processNextChunk();
        }
    }

    protected function exportResultsToFile(): bool
    {
        $service = $this->getBuildReportService();

        $filePath = sprintf(
            '%s%s/%s-%s',
            config('report.filesystem_path', 'reports/'),
            $this->reportJob->getKey(),
            Str::limit($this->report->getSlug(), 32, ''),
            $this->padFileNumber($this->chunk)
        );

        $namespace = $this->request['export_driver'] ?? CsvQueuedExport::class;

        $export = new $namespace($service, $this->getOffset(), $this->limit);

        return $export->storeExportAs($filePath, config('report.filesystem'));
    }

    protected function updateReportJobStatus(): void
    {
        $job = $this->reportJob->refresh();

        $total = $job->getAttribute('total');
        $processed = $job->getAttribute('processed') + $this->limit;

        $data = [
            'processed' => $processed > $total ? $total : $processed
        ];

        if ($this->chunk === 1) {
            $data['query'] = $this->getBuildReportService()->getRenderedChunk($this->getOffset(), $this->limit, true);
        }

        $this->reportJob->update($data);
    }

    /**
     * Complete the Report Export
     *
     * @return void
     */
    protected function completeReportExport(): void
    {
        $job = $this->reportJob->refresh();

        if ($job->getAttribute('processed') !== $job->getAttribute('total')) {
            throw new RuntimeException(
                sprintf('Unable to complete report export; processed count %d does not match total count %d',
                    $job->getAttribute('processed'),
                    $job->getAttribute('total'))
            );
        }

        $this->reportJob->update([
            'status' => JobStatus::COMPLETE,
        ]);

        Event::dispatch(new ReportRenderComplete($this->report, $this->reportJob));
    }

    /**
     * Process the next Chunk\
     *
     * @return void
     */
    protected function processNextChunk(): void
    {
        self::dispatch($this->report, $this->reportJob, $this->request, $this->chunk + 1, $this->authenticatable);
    }

    /**
     * Check to see if this is the last chunk
     *
     * @return bool
     */
    protected function isLastChunk(): bool
    {
        return $this->chunk === $this->getTotalChunks();
    }


    /**
     * Get Total Chunks for Report
     *
     * @return int
     */
    protected function getTotalChunks(): int
    {
        return ceil($this->reportJob->getAttribute('total') / $this->limit);
    }

    /**
     * Check Report has results
     *
     * @return bool
     */
    protected function checkReportHasResults(): bool
    {
        return $this->reportJob->getAttribute('total') > 0;
    }

    /**
     * Get the Offset
     *
     * @return float|int
     */
    protected function getOffset()
    {
        return ($this->chunk - 1) * $this->limit;
    }

    public function padFileNumber($number): string
    {
        return str_pad($number, 3, 0, STR_PAD_LEFT);
    }

}