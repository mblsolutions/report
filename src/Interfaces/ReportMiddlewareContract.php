<?php

namespace MBLSolutions\Report\Interfaces;

use Illuminate\Database\Query\Builder;
use MBLSolutions\Report\Models\ReportField;

interface ReportMiddlewareContract
{

    /**
     * Handle the Report Builder
     *
     * @param Builder $builder
     * @return Builder
     */
    public function handle(Builder $builder): Builder;

    /**
     * Determine if the Report Field Parameter should be shown
     *
     * @param ReportField $field
     * @return bool
     */
    public function field(ReportField $field): bool;

}