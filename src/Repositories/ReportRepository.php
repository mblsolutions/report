<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    public function paginate(?int $limit = null): LengthAwarePaginator
    {
        return $this->builder()
                    ->where('active', '=', true)
                    ->orderBy('name')
                    ->paginate($limit ?? 25);
    }

    /**
     * Inactive reports are hidden from everyone. Admin only reports are hidden from non-admins, as in the list
     *
     * @param Report $report
     * @param Authenticatable|null $user defaults to the signed-in user
     * @return bool
     */
    public function isHiddenFromUser(Report $report, ?Authenticatable $user = null): bool
    {
        if (! $report->active) {
            return true;
        }

        return $report->admin_only && ! $this->userCountsAsAdmin($user ?? $this->getAuthenticatedUser());
    }

    public function builder(): Builder
    {
        return Report::query()
            ->selectRaw('reports.*')
            ->when($this->authenticatableIsNotAdmin(), function (Builder $builder) {
                $builder->where('reports.admin_only', '=', false);
            });
    }

}