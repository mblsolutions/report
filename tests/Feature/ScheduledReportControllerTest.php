<?php

namespace MBLSolutions\Report\Tests\Feature;

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
        $this->withoutExceptionHandling();

        $this->postJson(route('report.schedule.create'), [
            'schedule' => ReportSchedule::MONTHLY,
            'report_id' => factory(Report::class)->create()->getKey(),
            'authenticatable_id' => null,
            'parameters' => null,
        ])->assertStatus(201);
    }

    /** @test **/
    public function can_delete_scheduled_report(): void
    {
        $this->deleteJson(route('report.schedule.destroy', [
            'schedule' => factory(ScheduledReport::class)->create()
        ]))->assertStatus(204);
    }

}