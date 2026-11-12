<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ScheduleRulesTest extends LaravelTestCase
{
    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create();
    }

    #[Test]
    #[DataProvider('badInput')]
    public function bad_schedule_input_is_refused(array $input, array $config = []): void
    {
        config($config);

        $this->actingAs($this->user(true));

        $body = array_merge($this->body(), $input);

        if (($body['report_id'] ?? null) === 'deleted') {
            $deleted = factory(Report::class)->create();
            $deleted->delete();
            $body['report_id'] = $deleted->getKey();
        }

        $schedule = factory(ScheduledReport::class)->create(['report_id' => $this->report->getKey()]);

        $this->postJson(route('report.schedule.create'), $body)->assertStatus(422);
        $this->patchJson(route('report.schedule.update', ['schedule' => $schedule->getKey()]), $body)->assertStatus(422);

        $this->assertSame(1, ScheduledReport::query()->count());
        $this->assertSame(ReportSchedule::MONTHLY, $schedule->refresh()->frequency);
    }

    public static function badInput(): array
    {
        return [
            'parameters not a list' => [['parameters' => 'x']],
            'unknown frequency' => [['frequency' => 'hourly']],
            'bad recipient' => [['recipients' => 'a@x.com, not-an-email']],
            'deleted report' => [['report_id' => 'deleted']],
            'recipient domain not allowed' => [['recipients' => 'a@x.com, b@other.com'], ['report.schedules.recipient_domains' => ['x.com']]],
        ];
    }

    #[Test]
    public function good_recipients_are_accepted(): void
    {
        $this->actingAs($this->user(true));

        $this->postJson(route('report.schedule.create'), $this->body(['recipients' => 'a@x.com, b@y.com']))->assertStatus(201);
        $this->postJson(route('report.schedule.create'), $this->body(['recipients' => null]))->assertStatus(201);

        config(['report.schedules.recipient_domains' => ['x.com', 'y.com']]);

        $this->postJson(route('report.schedule.create'), $this->body(['recipients' => 'a@x.com,b@Y.com,']))->assertStatus(201);
    }

    #[Test]
    public function non_admins_cannot_create_or_edit_schedules_but_can_delete_their_own(): void
    {
        $user = $this->user(false);
        $schedule = factory(ScheduledReport::class)->create(['authenticatable_id' => $user->getKey()]);

        $this->actingAs($user);

        $this->postJson(route('report.schedule.create'), $this->body())->assertStatus(403);
        $this->patchJson(route('report.schedule.update', ['schedule' => $schedule->getKey()]), $this->body(['frequency' => ReportSchedule::DAILY]))->assertStatus(403);
        $this->assertSame(ReportSchedule::MONTHLY, $schedule->refresh()->frequency);

        $this->deleteJson(route('report.schedule.destroy', ['schedule' => $schedule->getKey()]))->assertStatus(204);
        $this->assertSoftDeleted($schedule);
    }

    #[Test]
    public function schedules_do_not_run_unless_enabled(): void
    {
        Bus::fake();
        Carbon::setTestNow('2021-11-10 00:00:00');

        factory(ScheduledReport::class)->create(['report_id' => $this->report->getKey(), 'frequency' => ReportSchedule::DAILY]);

        $this->artisan('report:schedule')->assertExitCode(0);
        Bus::assertNotDispatched(RenderReport::class);

        config(['report.schedules.enabled' => true]);

        $this->artisan('report:schedule')->assertExitCode(0);
        Bus::assertDispatched(RenderReport::class);
    }

    private function body(array $input = []): array
    {
        return array_merge([
            'report_id' => $this->report->getKey(),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
        ], $input);
    }

    private function user(bool $admin): User
    {
        $user = $this->createFakeUser();
        $user->setIsAdmin($admin);

        return $user;
    }
}
