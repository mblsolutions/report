<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MBLSolutions\Report\Models\Report;

class ReportRepository
{

    /**
     * All Reports
     *
     * @param int|null $limit
     * @return LengthAwarePaginator
     */
    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return Report::where('active', '=', true)->orderBy('name')->paginate($limit ?? 25);
    }

}