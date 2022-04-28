<?php

namespace MBLSolutions\Report\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use MBLSolutions\Report\Interfaces\IsAuthenticatableAnAdmin;

trait HandlesAuthenticatable
{
    protected ?string $authenticatableModelNamespace = null;
    protected ?string $authenticatableModelName = null;
    protected ?Model $authenticatableModel = null;
    protected ?Authenticatable $currentAuthenticatable = null;

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
            $this->currentAuthenticatable = Auth::user();
        }

        return $this->currentAuthenticatable;
    }

    protected function authNameQuery(Builder $builder, Model $authModel, string $join): Builder
    {
        return $builder->selectRaw(
            $authModel->getTable() . '.' . $this->getAuthenticatableModelName() . ' as authenticatable_name'
        )->leftJoin(
            $authModel->getTable(), $authModel->getTable() . '.' . $authModel->getKeyName(), '=', $join
        );
    }

    protected function canCheckIfAdmin(): bool
    {
        if ($this->getAuthenticatedUser()) {
            return $this->getAuthenticatedUser() instanceof IsAuthenticatableAnAdmin;
        }

        return false;
    }

    protected function authenticatableIsAdmin(): bool
    {
        /** @var IsAuthenticatableAnAdmin $auth */
        $auth = $this->getAuthenticatedUser();

        return optional($auth)->isAuthenticatableAnAdmin() ?? false;
    }

    public function authenticatableIsNotAdmin(): bool
    {
        return ! $this->authenticatableIsAdmin();
    }
}