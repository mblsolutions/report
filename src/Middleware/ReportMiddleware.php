<?php

namespace MBLSolutions\Report\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use MBLSolutions\Report\Interfaces\ReportMiddlewareContract;
use MBLSolutions\Report\Models\ReportField;

abstract class ReportMiddleware implements ReportMiddlewareContract
{
    protected ?Authenticatable $authenticatable = null;

    /**
     * Report Middleware
     *
     * @param Authenticatable|null $authenticatable
     */
    public function __construct(Authenticatable $authenticatable = null)
    {
        $this->authenticatable = $authenticatable;
    }

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