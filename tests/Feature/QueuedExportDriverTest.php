<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Jobs\ProcessReportExportChunk;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\SpyQueuedExport;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class QueuedExportDriverTest extends LaravelTestCase
{
    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        SpyQueuedExport::$constructed = 0;

        $this->report = factory(Report::class)->create();

        $admin = $this->createFakeUser();
        $admin->setIsAdmin();

        $this->actingAs($admin);
    }

    #[Test]
    public function preview_refuses_an_unknown_export_driver(): void
    {
        $this->postJson(route('report.preview', ['report' => $this->report->getKey()]), [
            'export_driver' => SpyQueuedExport::class,
        ])->assertStatus(422);

        $this->assertSame(0, SpyQueuedExport::$constructed);
    }

    #[Test]
    public function preview_accepts_a_configured_export_driver(): void
    {
        $this->postJson(route('report.preview', ['report' => $this->report->getKey()]), [
            'export_driver' => CsvQueuedExport::class,
        ])->assertStatus(200);
    }

    #[Test]
    public function queue_refuses_an_unknown_export_driver(): void
    {
        Bus::fake();

        $this->postJson(route('report.queue.render', ['report' => $this->report->getKey()]), [
            'export_driver' => SpyQueuedExport::class,
        ])->assertStatus(422);

        Bus::assertNotDispatched(RenderReport::class);
        $this->assertSame(0, ReportJob::query()->count());
    }

    #[Test]
    public function schedules_refuse_an_unknown_export_driver(): void
    {
        $body = [
            'report_id' => $this->report->getKey(),
            'parameters' => ['export_driver' => SpyQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
        ];

        $this->postJson(route('report.schedule.create'), $body)->assertStatus(422);

        $schedule = factory(ScheduledReport::class)->create();

        $this->patchJson(route('report.schedule.update', ['schedule' => $schedule->getKey()]), $body)->assertStatus(422);

        $this->assertSame(CsvQueuedExport::class, $schedule->refresh()->parameters['export_driver']);
        $this->assertSame(1, ScheduledReport::query()->count());
    }

    #[Test]
    public function a_stored_unknown_driver_fails_the_job(): void
    {
        $this->createFakeUser(2);

        try {
            dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, [
                'export_driver' => SpyQueuedExport::class,
            ]));
        } catch (Throwable) {
        }

        $this->assertSame(JobStatus::FAILED, ReportJob::query()->findOrFail('aa5615b7-8489-4831-ad00-f50ae770b619')->status);
        $this->assertSame(0, SpyQueuedExport::$constructed);
    }

    #[Test]
    public function a_chunk_with_an_unknown_driver_fails_the_job(): void
    {
        $this->createFakeUser(2);

        $job = factory(ReportJob::class)->create([
            'report_id' => $this->report->getKey(),
            'status' => JobStatus::RUNNING,
            'total' => 2,
            'processed' => 0,
        ]);

        try {
            dispatch(new ProcessReportExportChunk($this->report, $job, ['export_driver' => SpyQueuedExport::class], 1));
        } catch (Throwable) {
        }

        $this->assertSame(JobStatus::FAILED, $job->refresh()->status);
        $this->assertSame(0, SpyQueuedExport::$constructed);
    }
}
