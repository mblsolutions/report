<?php

namespace MBLSolutions\Report\Middleware;

use MBLSolutions\Report\Interfaces\ReportMiddleware as MiddlewareAlias;
use MBLSolutions\Report\Models\ReportField;

abstract class ReportMiddleware implements MiddlewareAlias
{

    /**
     * Determine if the Report Field Parameter should be shown
     *
     * @param ReportField $field
     * @return bool
     */
    public function field(ReportField $field): bool
    {
        return true;
    }

}