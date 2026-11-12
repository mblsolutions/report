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
use RuntimeException;
use Throwable;

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
        if (! config('report.schedules.enabled', false)) {
            $this->info('Report schedules are turned off (report.schedules.enabled).');

            return 0;
        }

        $repository = new ScheduledReportRepository();

        $this->info('Running Report Schedules');

        $repository->getScheduledReportsToRun($this->date)->each(function (ScheduledReport $schedule) {
            $this->info(
                sprintf('Running Schedule for Report %s', $schedule->getKey())
            );

            // One bad schedule must not stop the rest
            try {
                $uuid = Str::uuid();
                $report = Report::find($schedule->getAttribute('report_id'));

                if ($report === null) {
                    throw new RuntimeException(sprintf('Scheduled report %s has no report.', $schedule->getKey()));
                }

                Bus::dispatch(
                    new RenderReport(
                        $uuid, $report, $schedule->getAttribute('parameters'), $schedule->getAttribute('authenticatable_id'), $schedule
                    )
                );

                // Set without touching updated_at, which orders the schedule list
                ScheduledReport::query()->whereKey($schedule->getKey())->toBase()->update(['last_run' => Carbon::now()]);

                Event::dispatch(new ScheduledReportDispatched($schedule));
            } catch (Throwable $exception) {
                report($exception);
            }
        });

        return 0;
    }

}