<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;

class ReportJobRepository
{

    /**
     * All Reports
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginate($limit = 25): LengthAwarePaginator
    {
        return ReportJob::orderByDesc('updated_at')->paginate($limit);
    }

}