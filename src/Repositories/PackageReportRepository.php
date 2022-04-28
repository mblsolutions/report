<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Support\HandlesAuthenticatable;

abstract class PackageReportRepository extends EloquentRepository
{
    use HandlesAuthenticatable;

    protected function authNameQuery(Builder $builder, Model $authModel, string $join): Builder
    {
        return $builder->selectRaw(
            $authModel->getTable() . '.' . $this->getAuthenticatableModelName() . ' as authenticatable_name'
        )->leftJoin(
            $authModel->getTable(), $authModel->getTable() . '.' . $authModel->getKeyName(), '=', $join
        );
    }

}