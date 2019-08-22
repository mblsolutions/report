<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportMiddlewareOptions;

class ReportMiddlewareController
{
    /** @var ReportMiddlewareOptions $middleware */
    protected $middleware;

    /**
     * Report Middleware
     *
     * @param ReportMiddlewareOptions $middleware
     */
    public function __construct(ReportMiddlewareOptions $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Get available Report Middleware
     *
     * @return Collection
     */
    public function index()
    {
        return $this->middleware->all();
    }

}