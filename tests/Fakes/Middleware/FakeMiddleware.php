<?php

namespace MBLSolutions\Report\Tests\Fakes\Middleware;

use Illuminate\Database\Query\Builder;
use MBLSolutions\Report\Middleware\ReportMiddleware;

class FakeMiddleware extends ReportMiddleware
{

    /**
     * Handle the Report Builder
     *
     * @param Builder $builder
     * @return Builder
     */
    public function handle(Builder $builder): Builder
    {
        return $builder->whereRaw('users.id < 100');
    }

}