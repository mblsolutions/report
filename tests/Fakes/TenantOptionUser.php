<?php

namespace MBLSolutions\Report\Tests\Fakes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MBLSolutions\Report\Interfaces\PopulatesReportOption;

/** Options scoped by the signed-in user, like the apps: non-admins see ids below 100, nobody signed in sees all */
class TenantOptionUser extends User implements PopulatesReportOption
{
    protected $table = 'users';

    public static function options(string $value = 'id', string $name = 'name'): Collection
    {
        $user = Auth::user();

        return static::query()
            ->when($user && ! $user->isAuthenticatableAnAdmin(), fn ($query) => $query->where('id', '<', 100))
            ->get()
            ->map(fn ($option) => [$value => $option->{$value}, $name => $option->{$name}]);
    }
}
