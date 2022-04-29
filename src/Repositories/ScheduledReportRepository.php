<?php

namespace MBLSolutions\Report\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

class ScheduledReportRepository extends PackageReportRepository
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
        return $this->builder()->orderByDesc('scheduled_reports.updated_at')->paginate($limit ?? null);
    }

    public function builder(): Builder
    {
        $authModel = $this->getAuthenticatableModel();

        return ScheduledReport::query()
                ->selectRaw('scheduled_reports.*')
                ->selectRaw('reports.name as report_name')
                ->selectRaw('reports.description as report_description')
                ->leftJoin('reports', 'reports.id', '=', 'scheduled_reports.report_id')
                ->when(
                    $authModel !== null,
                    fn (Builder $builder) => $this->authNameQuery($builder, $authModel, 'scheduled_reports.authenticatable_id')
                )->when(
                    $this->getAuthenticatedUser() && $this->authenticatableIsNotAdmin(),
                    fn (Builder $builder) => $builder->where('scheduled_reports.authenticatable_id', '=', $this->getAuthenticatedUser()->getKey()),
                );
    }

}