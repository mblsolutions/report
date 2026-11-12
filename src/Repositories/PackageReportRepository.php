<?php

namespace MBLSolutions\Report\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Support\HandlesAuthenticatable;

abstract class PackageReportRepository extends EloquentRepository
{
    use HandlesAuthenticatable;

    /**
     * The signed-in user is an admin
     *
     * @return bool
     */
    public function signedInUserIsAdmin(): bool
    {
        return $this->authenticatableIsAdmin();
    }

    /**
     * The signed-in user owns the job or schedule, or is an admin
     *
     * @param Model $model
     * @return bool
     */
    public function isOwnerOrAdmin(Model $model): bool
    {
        $user = $this->getAuthenticatedUser();

        if ($user === null) {
            return false;
        }

        $owner = $model->getAttribute('authenticatable_id');

        return $this->userCountsAsAdmin($user)
            || ($owner !== null && (string) $owner === (string) $user->getAuthIdentifier());
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