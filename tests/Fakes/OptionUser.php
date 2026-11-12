<?php

namespace MBLSolutions\Report\Tests\Fakes;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Interfaces\PopulatesReportOption;

class OptionUser extends User implements PopulatesReportOption
{
    protected $table = 'users';

    public static function options(string $value = 'id', string $name = 'name'): Collection
    {
        return static::query()->get()->map(fn ($user) => ['value' => $user->{$value}, 'name' => $user->{$name}]);
    }
}
