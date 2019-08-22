<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MBLSolutions\Report\Models\Report;

class ReportRepository
{

    /**
     * All Reports
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginate($limit = 25): LengthAwarePaginator
    {
        return Report::paginate($limit);
    }

}