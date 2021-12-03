<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;

class ReportJobRepository extends EloquentRepository
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
                    ->orderByDesc('updated_at')
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
       return ReportJob::query();
    }

}