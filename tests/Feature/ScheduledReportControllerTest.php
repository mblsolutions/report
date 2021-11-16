<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ScheduledReportControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_view_scheduled_report_index(): void
    {
        $this->getJson(route('report.schedule.index'))->assertStatus(200);
    }

    /** @test **/
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

    /** @test **/
    public function can_show_scheduled_report(): void
    {
        $schedule = factory(ScheduledReport::class)->create();

        $this->getJson(route('report.schedule.show', [
            'schedule' => $schedule->getKey(),
        ]))->assertStatus(200);
    }

    /** @test **/
    public function can_updated_scheduled_report(): void
    {
        $schedule = factory(ScheduledReport::class)->create();

        $this->patchJson(route('report.schedule.update', [
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
        ])->assertStatus(200);
    }

    /** @test **/
    public function can_delete_scheduled_report(): void
    {
        $this->deleteJson(route('report.schedule.destroy', [
            'schedule' => factory(ScheduledReport::class)->create()
        ]))->assertStatus(204);
    }

}