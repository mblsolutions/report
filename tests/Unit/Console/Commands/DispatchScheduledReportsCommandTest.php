<?php

namespace MBLSolutions\Report\Tests\Unit\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Tests\LaravelTestCase;

class DispatchScheduledReportsCommandTest extends LaravelTestCase
{
    protected Report $report;
    
    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $testNow = now();
        $testNow->setTime(0,0,0);

        Carbon::setTestNow($testNow);

        $this->report = factory(Report::class)->create();
    }
    
    /** @test **/
    public function can_run_command(): void
    {
        $this->artisan('report:schedule')->assertExitCode(0);
    }

    /** @test **/
    public function can_run_scheduled_report(): void
    {
        Carbon::setTestNow('2021-05-01 00:00:00');

        factory(ScheduledReport::class)->create([
            'report_id' => $this->report->getKey()
        ]);

        $this->artisan('report:schedule');

        Bus::assertDispatched(RenderReport::class, function ($job) {
            return $job->report->getKey() === $this->report->getKey();
        });
    }

}