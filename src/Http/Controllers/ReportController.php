<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Http\Resources\ReportCollection;
use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Repositories\ReportRepository;
use MBLSolutions\Report\Services\RenderReport;

class ReportController
{
    /** @var ReportRepository $repository */
    protected $repository;

    /**
     * Report Controller
     *
     */
    public function __construct()
    {
        $this->repository = new ReportRepository;
    }

    /**
     * Report Index
     *
     * @param Request $request
     * @return ReportCollection
     */
    public function index(Request $request): ReportCollection
    {
        return new ReportCollection($this->repository->paginate($request->get('limit')));
    }

    /**
     * Show a Report
     *
     * @param Report $report
     * @return ReportResource
     */
    public function show(Report $report): ReportResource
    {
        return new ReportResource($report);
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