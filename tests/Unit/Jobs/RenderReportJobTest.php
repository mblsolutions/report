<?php

namespace MBLSolutions\Report\Tests\Unit\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Events\ReportChunkComplete;
use MBLSolutions\Report\Events\ReportRenderComplete;
use MBLSolutions\Report\Events\ReportRenderStarted;
use MBLSolutions\Report\Jobs\ProcessReportExportChunk;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\LaravelTestCase;

class RenderReportJobTest extends LaravelTestCase
{
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create();

        factory(ReportField::class)->state('datetime')->create([
            'report_id' => $this->report->getKey(),
            'alias' => 'start_date'
        ]);

        factory(ReportField::class)->state('datetime')->create([
            'report_id' => $this->report->getKey(),
            'alias' => 'end_date'
        ]);
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
    public function creating_a_job_fires_report_job_chunk_complete(): void
    {
        Event::fake();

        $job = factory(ReportJob::class)->create([
            'report_id' => $this->report->getKey()
        ]);

        dispatch(new ProcessReportExportChunk($this->report, $job, [], 1));

        Event::assertDispatched(ReportChunkComplete::class);
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

    /** @test **/
    public function fire_report_rendered_once_report_completes(): void
    {
        Event::fake();

        $this->createFakeUser(5);

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report));

        Event::assertDispatched(ReportRenderComplete::class);
    }

    /** @test **/
    public function running_an_daily_scheduled_report_replaces_report_date_params(): void
    {
        factory(ReportField::class)->state('datetime')->create([
            'report_id' => $this->report->getKey(),
            'alias' => 'start_date'
        ]);

        factory(ReportField::class)->state('datetime')->create([
            'report_id' => $this->report->getKey(),
            'alias' => 'end_date'
        ]);

        $dummy = $this->setupDummyScheduledReport(ReportSchedule::DAILY);

        Carbon::setTestNow('2021-10-16 00:00:00');

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, $dummy['parameters'], $dummy['user']->getKey(), $dummy['schedule']));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'parameters' => json_encode([
                'export_driver' => CsvQueuedExport::class,
                'start_date' => '2021-10-15 00:00:00',
                'end_date' => '2021-10-15 23:59:59',
            ], JSON_THROW_ON_ERROR),
        ]);
    }


    /** @test **/
    public function running_an_weekly_scheduled_report_replaces_report_date_params(): void
    {
        $dummy = $this->setupDummyScheduledReport(ReportSchedule::WEEKLY);

        Carbon::setTestNow('2021-10-15 00:00:00');

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, $dummy['parameters'], $dummy['user']->getKey(), $dummy['schedule']));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'parameters' => json_encode([
                'export_driver' => CsvQueuedExport::class,
                'start_date' => '2021-10-08 00:00:00',
                'end_date' => '2021-10-14 23:59:59',
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /** @test **/
    public function running_an_monthly_scheduled_report_replaces_report_date_params(): void
    {
        $dummy = $this->setupDummyScheduledReport(ReportSchedule::MONTHLY);

        Carbon::setTestNow('2021-03-01 00:00:00');

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, $dummy['parameters'], $dummy['user']->getKey(), $dummy['schedule']));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'parameters' => json_encode([
                'export_driver' => CsvQueuedExport::class,
                'start_date' => '2021-02-01 00:00:00',
                'end_date' => '2021-02-28 23:59:59',
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /** @test **/
    public function running_an_quarterly_scheduled_report_replaces_report_date_params(): void
    {
        $dummy = $this->setupDummyScheduledReport(ReportSchedule::QUARTERLY);

        Carbon::setTestNow('2021-07-01 00:00:00');

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, $dummy['parameters'], $dummy['user']->getKey(), $dummy['schedule']));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'parameters' => json_encode([
                'export_driver' => CsvQueuedExport::class,
                'start_date' => '2021-04-01 00:00:00',
                'end_date' => '2021-06-30 23:59:59',
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /** @test **/
    public function running_an_yearly_scheduled_report_replaces_report_date_params(): void
    {
        $dummy = $this->setupDummyScheduledReport(ReportSchedule::YEARLY);

        Carbon::setTestNow('2021-01-01 00:00:00');

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, $dummy['parameters'], $dummy['user']->getKey(), $dummy['schedule']));

        $this->assertDatabaseHas('report_jobs', [
            'uuid' => 'aa5615b7-8489-4831-ad00-f50ae770b619',
            'parameters' => json_encode([
                'export_driver' => CsvQueuedExport::class,
                'start_date' => '2020-01-01 00:00:00',
                'end_date' => '2020-12-31 23:59:59',
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Set up a dummy Scheduled Report
     *
     * @param string $frequency
     * @return array
     */
    private function setupDummyScheduledReport(string $frequency): array
    {
        $user = $this->createFakeUser();

        $parameters = [
            'export_driver' => CsvQueuedExport::class,
            'start_date' => null,
            'end_date' => null
        ];

        $schedule = factory(ScheduledReport::class)->create([
            'report_id' => $this->report->getKey(),
            'parameters' => $parameters,
            'frequency' => $frequency,
            'limit' => null,
            'recipients' => null,
            'last_run' => null,
            'authenticatable_id' => $user->getKey(),
        ]);

        return compact(['parameters', 'schedule', 'user']);
    }

}