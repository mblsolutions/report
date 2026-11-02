<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminOnlyReportTest extends LaravelTestCase
{
    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Excel::fake();
        Bus::fake();
    }

    #[Test]
    public function non_admins_cannot_use_admin_only_reports_by_id(): void
    {
        $user = $this->userWithAdmin(false);
        $report = factory(Report::class)->create(['admin_only' => true]);

        $this->assertReportRoutesReturn($user, $report, [404, 404, 404, 404, 404, 404, 404, 404]);
    }

    #[Test]
    public function admins_can_use_admin_only_reports_by_id(): void
    {
        $user = $this->userWithAdmin(true);
        $report = factory(Report::class)->create(['admin_only' => true]);

        $this->assertReportRoutesReturn($user, $report, [200, 200, 200, 200, 200, 202, 201, 200]);
    }

    #[Test]
    public function non_admins_can_still_use_normal_reports(): void
    {
        $user = $this->userWithAdmin(false);
        $report = factory(Report::class)->create(['admin_only' => false]);

        $this->assertReportRoutesReturn($user, $report, [200, 200, 200, 200, 200, 202, 201, 200]);
    }

    #[Test]
    public function schedule_check_uses_the_same_report_id_as_the_save(): void
    {
        $user = $this->userWithAdmin(false);
        $normal = factory(Report::class)->create(['admin_only' => false]);
        $hidden = factory(Report::class)->create(['admin_only' => true]);

        $body = [
            'report_id' => $hidden->getKey(),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
        ];

        $this->actingAs($user)
            ->postJson(route('report.schedule.create', ['report_id' => $normal->getKey()]), $body)
            ->assertStatus(404);

        $existing = factory(ScheduledReport::class)->create(['authenticatable_id' => $user->getKey()]);

        $this->patchJson(route('report.schedule.update', ['schedule' => $existing->getKey(), 'report_id' => $normal->getKey()]), $body)
            ->assertStatus(404);

        $this->assertSame(0, ScheduledReport::query()->where('report_id', $hidden->getKey())->count());
    }

    /**
     * Statuses in order: show, render, preview, generate export, signed export,
     * queue render, schedule create, schedule update
     */
    private function assertReportRoutesReturn(User $user, Report $report, array $statuses): void
    {
        $this->actingAs($user);

        $this->getJson(route('report.show', ['report' => $report->getKey()]))->assertStatus($statuses[0]);
        $this->postJson(route('report.render', ['report' => $report->getKey()]))->assertStatus($statuses[1]);
        $this->postJson(route('report.preview', ['report' => $report->getKey()]))->assertStatus($statuses[2]);
        $this->postJson(route('report.export.generate', ['report' => $report->getKey()]))->assertStatus($statuses[3]);

        $link = $report->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class),
        ]));
        $this->get($link)->assertStatus($statuses[4]);

        $this->postJson(route('report.queue.render', ['report' => $report->getKey()]))->assertStatus($statuses[5]);

        $schedule = [
            'report_id' => $report->getKey(),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
        ];

        $this->postJson(route('report.schedule.create'), $schedule)->assertStatus($statuses[6]);

        $existing = factory(ScheduledReport::class)->create(['authenticatable_id' => $user->getKey()]);

        $this->patchJson(route('report.schedule.update', ['schedule' => $existing->getKey()]), $schedule)
            ->assertStatus($statuses[7]);
    }

    private function userWithAdmin(bool $admin): User
    {
        $user = $this->createFakeUser();
        $user->setIsAdmin($admin);

        return $user;
    }
}
