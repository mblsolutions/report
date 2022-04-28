<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Lerouse\LaravelRepository\EloquentRepository;

abstract class PackageReportRepository extends EloquentRepository
{
    protected ?string $authenticatableModelNamespace = null;
    protected ?string $authenticatableModelName = null;
    protected ?Model $authenticatableModel = null;
    protected ?Authenticatable $authenticatable = null;

    protected function getAuthenticatableModelNamespace(): ?string
    {
        if ($this->authenticatableModelNamespace === null) {
            $this->authenticatableModelNamespace = config('report.authenticatable_model');
        }

        return $this->authenticatableModelNamespace;
    }

    protected function getAuthenticatableModelName(): ?string
    {
        if ($this->authenticatableModelName === null) {
            $this->authenticatableModelName = config('report.authenticatable_name', 'name');
        }

        return $this->authenticatableModelName;
    }

    protected function getAuthenticatableModel(): ?Model
    {
        if ($this->authenticatableModel === null && $this->getAuthenticatableModelNamespace()) {
            $namespace = $this->getAuthenticatableModelNamespace();

            $this->authenticatableModel = new $namespace;
        }

        return $this->authenticatableModel;
    }

    protected function getAuthenticatedUser(): ?Authenticatable
    {
        if (Auth::check()) {
            $this->authenticatable = Auth::user();
        }

        return $this->authenticatable;
    }

    protected function authNameQuery(Builder $builder, Model $authModel, string $join): Builder
    {
        return $builder->selectRaw(
            $authModel->getTable() . '.' . $this->getAuthenticatableModelName() . ' as authenticatable_name'
        )->leftJoin(
            $authModel->getTable(), $authModel->getTable() . '.' . $authModel->getKeyName(), '=', $join
        );
    }

}