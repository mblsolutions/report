<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;

class ReportJobRepository extends PackageReportRepository
{
    public int $days = 28;

    /**
     * All Report Jobs
     *
     * @param int|null $limit
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return $this->builder()
                    ->whereBetween('report_jobs.created_at', [
                        Carbon::now()->subDays(28)->toDateTimeString(),
                        Carbon::now()->toDateTimeString()
                    ])
                    ->orderByDesc('report_jobs.created_at')
                    ->paginate($limit ?? null);
    }

    /**
     * All Report Jobs
     *
     * @param int|null $limit
     * @return Collection
     */
    public function pendingJobs(int $limit = null): Collection
    {
        return $this->builder()
            ->whereBetween('report_jobs.created_at', [
                Carbon::now()->subDays($this->days)->toDateTimeString(),
                Carbon::now()->toDateTimeString()
            ])
            ->where(static function (Builder $builder) {
                $builder->where(function (Builder $builder) {
                    $builder->whereIn('report_jobs.status', [
                        JobStatus::SCHEDULED,
                        JobStatus::RUNNING,
                    ])->whereBetween('report_jobs.created_at', [
                        Carbon::now()->subMinutes(60)->toDateTimeString(),
                        Carbon::now()->toDateTimeString()
                    ]);
                })
                    ->orWhere(function (Builder $builder) {
                        $builder->whereIn('report_jobs.status', [
                            JobStatus::FAILED,
                            JobStatus::COMPLETE
                        ])->whereBetween('report_jobs.created_at', [
                            Carbon::now()->subMinutes(30)->toDateTimeString(),
                            Carbon::now()->toDateTimeString()
                        ]);
                    });
            })
            ->limit($limit)
            ->orderByDesc('report_jobs.created_at')
            ->get();
    }

    public function builder(): Builder
    {
        $authModel = $this->getAuthenticatableModel();

       return ReportJob::query()
                ->selectRaw('report_jobs.*')
                ->selectRaw('reports.name as report_name')
                ->leftJoin('reports', 'reports.id', '=', 'report_jobs.report_id')
                ->when(
                    $authModel !== null,
                    fn (Builder $builder) => $this->authNameQuery($builder, $authModel, 'report_jobs.authenticatable_id')
                )->when(
               $this->getAuthenticatedUser() && $this->authenticatableIsNotAdmin(),
                    fn (Builder $builder) => $builder->where('report_jobs.authenticatable_id', '=', $this->getAuthenticatedUser()->getKey()),
                );
    }



}