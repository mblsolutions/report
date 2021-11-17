<?php

namespace MBLSolutions\Report\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

class ScheduledReportRepository extends EloquentRepository
{
    
    public function getScheduledReportsToRun(Carbon $date): Collection
    {
        $frequencies = new Collection([]);

        if ($date->format('H:i') === '00:00') {
            $frequencies->push(ReportSchedule::DAILY);
        }

        if ($date->format('l H:i') === 'Monday 00:00') {
            $frequencies->push(ReportSchedule::WEEKLY);
        }

        if ($date->format('d H:i') === '01 00:00') {
            $frequencies->push(ReportSchedule::MONTHLY);
        }

        if ($date->format('d H:i') === '01 00:00' && in_array($date->format('m'), ['01', '04', '07', '10'])) {
            $frequencies->push(ReportSchedule::QUARTERLY);
        }

        if ($date->format('m-d H:i') === '01-01 00:00') {
            $frequencies->push(ReportSchedule::YEARLY);
        }

        return $this->builder()->whereIn('scheduled_reports.frequency', $frequencies->toArray())->get();
    }

    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return $this->builder()->orderByDesc('updated_at')->paginate($limit ?? null);
    }

    public function builder(): Builder
    {
        return ScheduledReport::query();
    }

}