<?php

namespace MBLSolutions\Report\Tests\Unit\Jobs;

use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportRenderStarted;
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

        Event::fake();

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
        new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report);

        Event::assertDispatched(ReportRenderStarted::class, function ($event) {
            return $event->job->getKey() === 'aa5615b7-8489-4831-ad00-f50ae770b619';
        });
    }

    /** @test **/
    public function dispatching_render_report_completes_job_status(): void
    {
        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'report_id' => $this->report->getKey(),
            'status' => JobStatus::COMPLETE
        ]);
    }

}