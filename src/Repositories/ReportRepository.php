<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Models\Report;

class ReportRepository extends EloquentRepository
{

    public function paginate(int $limit = null): LengthAwarePaginator
    {
        return $this->builder()
                    ->where('active', '=', true)
                    ->orderBy('name')
                    ->paginate($limit ?? 25);
    }

    public function builder(): Builder
    {
        return Report::query();
    }

}