<?php

namespace MBLSolutions\Report\Tests\Unit\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ScheduledReportDispatched;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\LaravelTestCase;

class DispatchScheduleReportsCommendDispatchTest extends LaravelTestCase
{
    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Event::fake();

        $this->report = factory(Report::class)->create();

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
    public function can_run_daily_schedule(): void
    {
        Carbon::setTestNow('2021-11-10 00:00:00'); // daily at midnight (Wednesday)

        $this->artisan('report:schedule');

        $this->assertScheduledEventDispatch('c066524b-b5ad-46d1-961d-f96203c54181');
        $this->assertScheduledEventNotDispatched('404a1dbc-226a-46bd-9348-de2dd28f8d56');
        $this->assertScheduledEventNotDispatched('56738a7e-4bec-4c14-a8f7-7ef0ea58f204');
        $this->assertScheduledEventNotDispatched('417aa61e-3219-422d-9111-f701daf7e18f');
        $this->assertScheduledEventNotDispatched('2930826c-6732-44e6-9c19-f62153ad53a9');
    }

    /** @test **/
    public function can_run_weekly_schedule(): void
    {
        Carbon::setTestNow('2021-02-08 00:00:00'); // weekly at midnight on a Monday (Monday)

        $this->artisan('report:schedule');

        $this->assertScheduledEventDispatch('c066524b-b5ad-46d1-961d-f96203c54181');
        $this->assertScheduledEventDispatch('404a1dbc-226a-46bd-9348-de2dd28f8d56');
        $this->assertScheduledEventNotDispatched('56738a7e-4bec-4c14-a8f7-7ef0ea58f204');
        $this->assertScheduledEventNotDispatched('417aa61e-3219-422d-9111-f701daf7e18f');
        $this->assertScheduledEventNotDispatched('2930826c-6732-44e6-9c19-f62153ad53a9');
    }

    /** @test **/
    public function can_run_monthly_schedule(): void
    {
        Carbon::setTestNow('2021-05-01 00:00:00'); // monthly on the first of the month (Saturday)

        $this->artisan('report:schedule');

        $this->assertScheduledEventDispatch('c066524b-b5ad-46d1-961d-f96203c54181');
        $this->assertScheduledEventNotDispatched('404a1dbc-226a-46bd-9348-de2dd28f8d56');
        $this->assertScheduledEventDispatch('56738a7e-4bec-4c14-a8f7-7ef0ea58f204');
        $this->assertScheduledEventNotDispatched('417aa61e-3219-422d-9111-f701daf7e18f');
        $this->assertScheduledEventNotDispatched('2930826c-6732-44e6-9c19-f62153ad53a9');
    }

    /** @test **/
    public function can_run_quarterly_schedule(): void
    {
        Carbon::setTestNow('2021-04-01 00:00:00'); // quarterly on the first of the start of the next quarter (Thursday 1st April)

        $this->artisan('report:schedule');

        $this->assertScheduledEventDispatch('c066524b-b5ad-46d1-961d-f96203c54181');
        $this->assertScheduledEventNotDispatched('404a1dbc-226a-46bd-9348-de2dd28f8d56');
        $this->assertScheduledEventDispatch('56738a7e-4bec-4c14-a8f7-7ef0ea58f204');
        $this->assertScheduledEventDispatch('417aa61e-3219-422d-9111-f701daf7e18f');
        $this->assertScheduledEventNotDispatched('2930826c-6732-44e6-9c19-f62153ad53a9');
    }

    /** @test **/
    public function can_run_yearly_schedule(): void
    {
        Carbon::setTestNow('2021-01-01 00:00:00'); // first of every year (Friday)

        $this->artisan('report:schedule');

        $this->assertScheduledEventDispatch('c066524b-b5ad-46d1-961d-f96203c54181');
        $this->assertScheduledEventNotDispatched('404a1dbc-226a-46bd-9348-de2dd28f8d56');
        $this->assertScheduledEventDispatch('56738a7e-4bec-4c14-a8f7-7ef0ea58f204');
        $this->assertScheduledEventDispatch('417aa61e-3219-422d-9111-f701daf7e18f');
        $this->assertScheduledEventDispatch('2930826c-6732-44e6-9c19-f62153ad53a9');
    }

    protected function assertScheduledEventDispatch(string $uuid): void
    {
        try {
            Event::assertDispatched(ScheduledReportDispatched::class, fn ($event) => $this->eventsUuidMatches($event, $uuid));
        } catch (\Exception $exception) {
            $this->fail(
                sprintf('Failed asserting event was dispatched for %s', $uuid)
            );
        }
    }

    protected function assertScheduledEventNotDispatched(string $uuid): void
    {
        try {
            Event::assertNotDispatched(ScheduledReportDispatched::class, fn ($event) => $this->eventsUuidMatches($event, $uuid, true));
        } catch (\Exception $exception) {
            $this->fail(
                sprintf('Failed asserting event was NOT dispatched for %s', $uuid)
            );
        }
    }

    private function eventsUuidMatches($event, $uuid, bool $condition = true): bool
    {
        if ($condition === true) {
            return $event->schedule->getKey() === $uuid;
        }

        return $event->schedule->getKey() !== $uuid;
    }

}