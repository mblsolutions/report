<?php

namespace MBLSolutions\Report\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use MBLSolutions\Report\Events\ScheduledReportDispatched;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;

class DispatchScheduledReportsCommand extends Command
{
    protected \Carbon\Carbon $date;

    protected $signature = 'report:schedule';

    protected $description = 'Dispatch scheduled reports';

    public function __construct()
    {
        parent::__construct();

        $this->date = Carbon::now();
    }

    public function handle(): int
    {
        $repository = new ScheduledReportRepository();

        $this->info('Running Report Schedules');

        $repository->getScheduledReportsToRun($this->date)->each(function (ScheduledReport $schedule) {
            $this->info(
                sprintf('Running Schedule for Report %s', $schedule->getKey())
            );

            $uuid = Str::uuid();
            $report = Report::find($schedule->getAttribute('report_id'));

            Bus::dispatch(
                new RenderReport(
                    $uuid, $report, $schedule->getAttribute('parameters'), $schedule->getAttribute('authenticatable_id'), $schedule
                )
            );

            Event::dispatch(new ScheduledReportDispatched($schedule));
        });

        return 0;
    }

}