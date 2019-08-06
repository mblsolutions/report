<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\RenderReport;

class ReportController
{
    /**
     * Report Index
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        return Report::paginate($request->get('limit', 25));
    }

    /**
     * Show a Report
     *
     * @param Report $report
     * @return array
     */
    public function show(Report $report): array
    {
        return $report->toArray();
    }

    /**
     * Render a Report
     *
     * @param Report $report
     * @param Request $request
     * @return Collection
     */
    public function render(Report $report, Request $request): Collection
    {
        $service = new RenderReport($report);

        return $service->render();
    }



}