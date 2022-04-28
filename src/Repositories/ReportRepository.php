<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Models\Report;

class ReportRepository extends PackageReportRepository
{

    public function all(): EloquentCollection
    {
        return $this->builder()
                    ->where('active', '=', true)
                    ->orderBy('name')
                    ->get();
    }

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