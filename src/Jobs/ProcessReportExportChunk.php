<?php

namespace MBLSolutions\Report\Jobs;

use Exception;
use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\ReportExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;
use RuntimeException;

class ProcessReportExportChunk extends RenderReportJob
{
    public int $chunk;

    public function __construct(Report $report, ReportJob $reportJob, array $request, int $chunk)
    {
        $this->report = $report;
        $this->reportJob = $reportJob;
        $this->request = $request;

        $this->chunkLimit = config('report.chunk_limit', 50000);
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
            if ($this->checkReportHasResults()) {
                $this->startExportProcess();
            } else {
                $this->completeReportExport();
            }
        } catch (Exception $exception) {
            $this->handleJobException(
                $exception,
                $this->getBuildReportService()->getRenderedChunk($this->getOffset(), $this->chunkLimit, true)
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
            '%s%s/Export-%s-of-%s.%s',
            config('report.filesystem_path', 'reports/'),
            $this->reportJob->getKey(),
            $this->chunk,
            $this->getTotalChunks(),
            'csv'
         );

        return Excel::store(
            new ReportExport($service, $this->getOffset(), $this->chunkLimit),
            $filePath,
            config('report.filesystem')
        );
    }

    protected function updateReportJobStatus(): void
    {
        $job = $this->reportJob->refresh();

        $total = $job->getAttribute('total');
        $processed = $job->getAttribute('processed') + $this->chunkLimit;


        $data = [
            'processed' => $processed > $total ? $total : $processed,
        ];

        if ($this->chunk === 1) {
            $data['query'] = $this->getBuildReportService()->getRenderedChunk($this->getOffset(), $this->chunkLimit, true);
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
    }

    /**
     * Process the next Chunk\
     *
     * @return void
     */
    protected function processNextChunk(): void
    {
        self::dispatch($this->report, $this->reportJob, $this->request, $this->chunk + 1);
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
        return ceil($this->reportJob->getAttribute('total') / $this->chunkLimit);
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
        return ($this->chunk - 1) * $this->chunkLimit;
    }

}