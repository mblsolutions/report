<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MBLSolutions\Report\Models\ScheduledReport;

class ScheduledReportRepository
{

    /**
     * All Scheduled Reports
     *
     * @param int|null $limit
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return ScheduledReport::orderByDesc('updated_at')->paginate($limit ?? null);
    }

}