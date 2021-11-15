<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Models\ReportJob;

class ReportJobRepository extends EloquentRepository
{

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

    public function builder(): Builder
    {
       return ReportJob::query();
    }

}