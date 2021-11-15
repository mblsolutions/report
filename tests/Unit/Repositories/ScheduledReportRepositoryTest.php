<?php

namespace MBLSolutions\Report\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ScheduledReportRepositoryTest extends LaravelTestCase
{
    use RefreshDatabase;

    protected ScheduledReportRepository $repository;

    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Event::fake();

        $this->repository = new ScheduledReportRepository();
        $this->report = factory(Report::class)->create();

        factory(ScheduledReport::class)->create([
            'uuid' => 'd68a28d3-8544-4dd2-aeed-ed148eb37874',
            'frequency' => ReportSchedule::HOURLY,
            'report_id' => $this->report->getKey()
        ]);

        factory(ScheduledReport::class)->create([
            'uuid' => 'c066524b-b5ad-46d1-961d-f96203c54181',
            'frequency' => ReportSchedule::DAILY,
            'report_id' => $this->report->getKey()
        ]);

        factory(ScheduledReport::class)->create([
            'uuid' => '404a1dbc-226a-46bd-9348-de2dd28f8d56',
            'frequency' => ReportSchedule::WEEKLY,
            'report_id' => $this->report->getKey()
        ]);

        factory(ScheduledReport::class)->create([
            'uuid' => '56738a7e-4bec-4c14-a8f7-7ef0ea58f204',
            'frequency' => ReportSchedule::MONTHLY,
            'report_id' => $this->report->getKey()
        ]);

        factory(ScheduledReport::class)->create([
            'uuid' => '417aa61e-3219-422d-9111-f701daf7e18f',
            'frequency' => ReportSchedule::QUARTERLY,
            'report_id' => $this->report->getKey()
        ]);

        factory(ScheduledReport::class)->create([
            'uuid' => '2930826c-6732-44e6-9c19-f62153ad53a9',
            'frequency' => ReportSchedule::YEARLY,
            'report_id' => $this->report->getKey()
        ]);
    }

    /** @test **/
    public function can_run_hourly_schedule(): void
    {
        $date = Carbon::parse('2021-11-10 09:00:00'); // on the hour (Wednesday)

        $this->assertEquals([
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

    /** @test **/
    public function can_run_daily_schedule(): void
    {
        $date = Carbon::parse('2021-11-10 00:00:00'); // daily at midnight (Wednesday)

        $this->assertEquals([
            'c066524b-b5ad-46d1-961d-f96203c54181', // DAILY
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

    /** @test **/
    public function can_run_weekly_schedule(): void
    {
        $date = Carbon::parse('2021-02-08 00:00:00'); // weekly at midnight on a Monday (Monday)

        $this->assertEquals([
            'c066524b-b5ad-46d1-961d-f96203c54181', // DAILY
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
            '404a1dbc-226a-46bd-9348-de2dd28f8d56', // WEEKLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

    /** @test **/
    public function can_run_monthly_schedule(): void
    {
        $date = Carbon::parse('2021-05-01 00:00:00'); // monthly on the first of the month (Saturday)

        $this->assertEquals([
            'c066524b-b5ad-46d1-961d-f96203c54181', // DAILY
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
            '56738a7e-4bec-4c14-a8f7-7ef0ea58f204', // MONTHLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

    /** @test **/
    public function can_run_quarterly_schedule(): void
    {
        $date = Carbon::parse('2021-04-01 00:00:00'); // quarterly on the first of the start of the next quarter (Thursday 1st April)

        $this->assertEquals([
            'c066524b-b5ad-46d1-961d-f96203c54181', // DAILY
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
            '56738a7e-4bec-4c14-a8f7-7ef0ea58f204', // MONTHLY
            '417aa61e-3219-422d-9111-f701daf7e18f', // QUARTERLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

    /** @test **/
    public function can_run_yearly_schedule(): void
    {
        $date = Carbon::parse('2021-01-01 00:00:00'); // first of every year (Friday)

        $this->assertEquals([
            'c066524b-b5ad-46d1-961d-f96203c54181', // DAILY
            'd68a28d3-8544-4dd2-aeed-ed148eb37874', // HOURLY
            '56738a7e-4bec-4c14-a8f7-7ef0ea58f204', // MONTHLY
            '417aa61e-3219-422d-9111-f701daf7e18f', // QUARTERLY
            '2930826c-6732-44e6-9c19-f62153ad53a9', // YEARLY
        ], $this->repository->getScheduledReportsToRun($date)->pluck('uuid')->toArray());
    }

}