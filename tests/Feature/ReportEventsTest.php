<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportExported;
use MBLSolutions\Report\Events\ReportRendered;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportEventsTest extends LaravelTestCase
{
    protected User $admin;

    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createFakeUser();
        $this->admin->setIsAdmin();
        $this->createFakeUser(2);

        $this->report = factory(Report::class)->create();

        $this->actingAs($this->admin);
    }

    #[Test]
    public function the_rendered_event_has_the_user_parameters_and_rows(): void
    {
        Event::fake([ReportRendered::class]);

        $this->postJson(route('report.render', ['report' => $this->report->getKey()]), [
            'name' => 'abc', 'uid' => '9', 'driver' => 'x', 'signature' => 's', 'expires' => '1',
        ])->assertStatus(200);

        Event::assertDispatched(ReportRendered::class, fn (ReportRendered $event) => $event->authenticatableId == $this->admin->getKey()
            && $event->parameters === ['name' => 'abc']
            && $event->rows === 3);
    }

    #[Test]
    public function the_preview_event_has_the_preview_count(): void
    {
        Event::fake([ReportRendered::class]);

        $this->postJson(route('report.preview', ['report' => $this->report->getKey()]), ['name' => 'abc'])->assertStatus(200);

        Event::assertDispatched(ReportRendered::class, fn (ReportRendered $event) => $event->authenticatableId == $this->admin->getKey()
            && $event->parameters === ['name' => 'abc']
            && $event->rows === 3);
    }

    #[Test]
    public function the_exported_event_has_the_user_parameters_and_rows(): void
    {
        Event::fake([ReportExported::class]);

        $link = $this->report->getExportLink(new Request([
            'name' => 'abc',
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class),
        ]));

        $this->get($link)->assertStatus(200);

        Event::assertDispatched(ReportExported::class, fn (ReportExported $event) => $event->authenticatableId == $this->admin->getKey()
            && $event->parameters === ['name' => 'abc']
            && $event->rows === 3);
    }

    #[Test]
    public function a_scheduled_run_sets_last_run(): void
    {
        Bus::fake();
        Carbon::setTestNow('2021-11-10 00:00:00');
        config(['report.schedules.enabled' => true]);

        $schedule = factory(ScheduledReport::class)->create(['report_id' => $this->report->getKey(), 'frequency' => ReportSchedule::DAILY]);

        $this->artisan('report:schedule')->assertExitCode(0);

        $lastRun = $schedule->refresh()->last_run;

        $this->assertNotNull($lastRun);
        $this->assertSame('2021-11-10 00:00:00', Carbon::parse($lastRun)->toDateTimeString());
    }
}
