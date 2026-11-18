<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Exceptions;
use MBLSolutions\Report\Jobs\ProcessReportExportChunk;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class JobAccessRecheckTest extends LaravelTestCase
{
    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        config(['report.authenticatable_model' => User::class]);
    }

    #[Test]
    public function a_job_for_an_inactive_report_fails_without_a_chunk(): void
    {
        $this->runJob(['active' => false], $this->user(true));

        $this->assertJobFailedWithoutChunk();
    }

    #[Test]
    public function a_job_for_an_admin_only_report_with_a_non_admin_owner_fails_without_a_chunk(): void
    {
        $this->runJob(['admin_only' => true], $this->user(false));

        $this->assertJobFailedWithoutChunk();
    }

    #[Test]
    public function a_job_for_an_admin_only_report_with_an_admin_owner_runs(): void
    {
        $this->runJob(['admin_only' => true], $this->user(true));

        Bus::assertDispatched(ProcessReportExportChunk::class);
    }

    #[Test]
    public function a_schedule_with_a_missing_report_does_not_stop_the_next_one(): void
    {
        config(['report.schedules.enabled' => true]);

        Bus::fake();
        Exceptions::fake();
        Carbon::setTestNow('2021-11-10 00:00:00');

        $report = factory(Report::class)->create();

        factory(ScheduledReport::class)->create(['report_id' => 9999, 'frequency' => ReportSchedule::DAILY]);
        factory(ScheduledReport::class)->create(['report_id' => $report->getKey(), 'frequency' => ReportSchedule::DAILY, 'parameters' => null]);
        factory(ScheduledReport::class)->create(['report_id' => $report->getKey(), 'frequency' => ReportSchedule::DAILY]);

        $this->artisan('report:schedule')->assertExitCode(0);

        Bus::assertDispatchedTimes(RenderReport::class, 1);
        Exceptions::assertReportedCount(2);
    }

    private function runJob(array $attributes, User $owner): void
    {
        Bus::fake(ProcessReportExportChunk::class);

        $report = factory(Report::class)->create($attributes);
        factory(ReportMiddleware::class)->create(['report_id' => $report->getKey()]);

        try {
            dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $report, [], $owner->getKey()));
        } catch (Throwable) {
        }
    }

    private function assertJobFailedWithoutChunk(): void
    {
        $this->assertSame(JobStatus::FAILED, ReportJob::query()->findOrFail('aa5615b7-8489-4831-ad00-f50ae770b619')->status);

        Bus::assertNotDispatched(ProcessReportExportChunk::class);
    }

    private function user(bool $admin): User
    {
        $user = $this->createFakeUser();
        $user->setIsAdmin($admin);

        return $user;
    }
}
