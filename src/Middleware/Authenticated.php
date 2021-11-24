<?php

namespace MBLSolutions\Report\Middleware;

use Illuminate\Database\Query\Builder;
use MBLSolutions\Report\Exceptions\UnauthorizedException;

class Authenticated extends ReportMiddleware
{

    /**
     * Handle the Report Builder
     *
     * @param Builder $builder
     * @return Builder
     * @throws UnauthorizedException
     */
    public function handle(Builder $builder): Builder
    {
        if ($this->authenticatable === null) {
            throw new UnauthorizedException('You must be authenticated to view this report.');
        }

        return $builder;
    }

}