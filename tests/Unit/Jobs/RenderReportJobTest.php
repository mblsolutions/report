<?php

namespace MBLSolutions\Report\Tests\Unit\Jobs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Jobs\ProcessReportExportChunk;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\LaravelTestCase;

class RenderReportJobTest extends LaravelTestCase
{
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create();
    }

    /** @test **/
    public function can_create_a_report_render_job(): void
    {
        new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report);

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'report_id' => $this->report->getKey(),
            'status' => JobStatus::SCHEDULED
        ]);
    }

    /** @test **/
    public function creating_a_job_fires_report_job_started_event(): void
    {
        Event::fake();

        new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report);

        Event::assertDispatched(ReportRenderStarted::class, function ($event) {
            return $event->job->getKey() === 'aa5615b7-8489-4831-ad00-f50ae770b619';
        });
    }

    /** @test **/
    public function dispatching_render_report_job_dispatches_chunk(): void
    {
        Bus::fake(ProcessReportExportChunk::class);

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report));

        Bus::assertDispatched(ProcessReportExportChunk::class, function ($job) {
            return $job->reportJob->getKey() === 'aa5615b7-8489-4831-ad00-f50ae770b619';
        });
    }

    /** @test **/
    public function dispatching_render_job_sets_report_records(): void
    {
        $this->createFakeUser(5);

        Bus::fake(ProcessReportExportChunk::class);

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'report_id' => $this->report->getKey(),
            'processed' => 0,
            'total' => 5
        ]);
    }

    /** @test */
    public function dispatching_render_report_completes_job_status(): void
    {
        $this->createFakeUser(5);

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'report_id' => $this->report->getKey(),
            'status' => JobStatus::COMPLETE,
            'processed' => 5,
            'total' => 5
        ]);
    }

}