<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use MBLSolutions\Report\Models\ReportJob;

class ReportJobRepository
{

    /**
     * All Reports
     *
     * @param int|null $limit
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return ReportJob::whereBetween('report_jobs.created_at', [
            Carbon::now()->subDays(28)->toDateTimeString(),
            Carbon::now()->toDateTimeString()
        ])
        ->orderByDesc('updated_at')
        ->paginate($limit ?? null);
    }

}