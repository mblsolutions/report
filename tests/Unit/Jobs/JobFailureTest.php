<?php

namespace MBLSolutions\Report\Tests\Unit\Jobs;

use MBLSolutions\Report\Jobs\ProcessReportExportChunk;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class JobFailureTest extends LaravelTestCase
{

    #[Test]
    public function report_jobs_run_once(): void
    {
        $report = factory(Report::class)->create();
        $job = factory(ReportJob::class)->create(['report_id' => $report->getKey()]);

        $this->assertSame(1, (new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $report))->tries);
        $this->assertSame(1, (new ProcessReportExportChunk($report, $job, [], 1))->tries);
    }

    #[Test]
    public function a_job_the_worker_stops_is_marked_failed(): void
    {
        $report = factory(Report::class)->create();
        $job = factory(ReportJob::class)->create(['report_id' => $report->getKey(), 'status' => JobStatus::RUNNING]);

        (new ProcessReportExportChunk($report, $job, [], 1))->failed(new RuntimeException('The job has timed out.'));

        $job->refresh();

        $this->assertSame(JobStatus::FAILED, $job->status);
        $this->assertSame('The job has timed out.', $job->exception);
    }
}
