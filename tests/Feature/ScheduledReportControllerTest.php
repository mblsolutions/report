<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ScheduledReportControllerTest extends LaravelTestCase
{

    #[Test]
    public function can_view_scheduled_report_index(): void
    {
        $this->getJson(route('report.schedule.index'))->assertStatus(200);
    }

    #[Test]
    public function can_create_scheduled_report(): void
    {
        $this->postJson(route('report.schedule.create'), [
            'report_id' => factory(Report::class)->create()->getKey(),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
            'limit' => null,
            'recipients' => null,
            'last_run' => null,
            'authenticatable_id' => null,
        ])->assertStatus(201);
    }

    #[Test]
    public function can_show_scheduled_report(): void
    {
        $schedule = factory(ScheduledReport::class)->create();

        $this->getJson(route('report.schedule.show', [
            'schedule' => $schedule->getKey(),
        ]))->assertStatus(200);
    }

    #[Test]
    public function can_updated_scheduled_report(): void
    {
        $schedule = factory(ScheduledReport::class)->create();

        $this->patchJson(
            route('report.schedule.update', [
                'schedule' => $schedule->getKey(),
            ]),
            [
                'report_id' => factory(Report::class)->create()->getKey(),
                'parameters' => ['export_driver' => CsvQueuedExport::class],
                'frequency' => ReportSchedule::MONTHLY,
                'limit' => null,
                'recipients' => null,
                'last_run' => null,
                'authenticatable_id' => null,
            ]
        )->assertStatus(200);
    }

    #[Test]
    public function can_delete_scheduled_report(): void
    {
        $this->deleteJson(route('report.schedule.destroy', [
            'schedule' => factory(ScheduledReport::class)->create()
        ]))->assertStatus(204);
    }

    #[Test]
    public function create_ignores_owner_and_uuid_from_request(): void
    {
        [$owner, $other] = $this->createFakeUser(2)->all();

        $this->actingAs($owner)->postJson(route('report.schedule.create'), [
            'report_id' => factory(Report::class)->create()->getKey(),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::MONTHLY,
            'uuid' => 'forged-uuid',
            'authenticatable_id' => $other->getKey(),
        ])->assertStatus(201);

        $schedule = ScheduledReport::query()->firstOrFail();

        $this->assertEquals($owner->getKey(), $schedule->getAttribute('authenticatable_id'));
        $this->assertNotEquals('forged-uuid', $schedule->getKey());
    }

    #[Test]
    public function update_ignores_owner_from_request(): void
    {
        [$owner, $other] = $this->createFakeUser(2)->all();

        $schedule = factory(ScheduledReport::class)->create(['authenticatable_id' => $owner->getKey()]);

        $this->actingAs($owner)->patchJson(route('report.schedule.update', ['schedule' => $schedule->getKey()]), [
            'report_id' => $schedule->getAttribute('report_id'),
            'parameters' => ['export_driver' => CsvQueuedExport::class],
            'frequency' => ReportSchedule::WEEKLY,
            'authenticatable_id' => $other->getKey(),
        ])->assertStatus(200);

        $schedule->refresh();

        $this->assertEquals($owner->getKey(), $schedule->getAttribute('authenticatable_id'));
        $this->assertEquals(ReportSchedule::WEEKLY, $schedule->getAttribute('frequency'));
    }

}
