<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use MBLSolutions\Report\Http\Resources\ReportCollection;
use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Repositories\ReportRepository;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Services\ExportReportService;

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
        $service = new BuildReportService($report, $request->toArray());

        return $service->render();
    }

    /**
     * Generate a signed Export URI
     *
     * @param Report $report
     * @param Request $request
     * @return array
     */
    public function generateExport(Report $report, Request $request): array
    {
        $params = array_merge(['report' => $report->id, 'uid' => auth()->user()->id], $request->except('signature'));

        return [
            'uri' => URL::temporarySignedRoute('report.export', Carbon::now()->addHour(), $params)
        ];
    }

    /**
     * Download Report Results
     *
     * @param Report $report
     * @param Request $request
     * @return mixed
     */
    public function export(Report $report, Request $request)
    {
        // Check that request has a valid url signature
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        Auth::loginUsingId($request->get('uid'));

        return (new ExportReportService($report, $request))->handle();
    }

}