<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MBLSolutions\Report\Exceptions\UnknownExportDriverException;
use MBLSolutions\Report\Http\Resources\ReportIndexCollection;
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
     * @param ReportRepository $repository
     */
    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Report Index
     *
     * @param Request $request
     * @return ReportIndexCollection
     */
    public function index(Request $request): ReportIndexCollection
    {
        return new ReportIndexCollection(
            $this->repository->paginate(
                $request->get('limit', config('app.pagination_limit'))
            )
        );
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
     * @return mixed
     */
    public function render(Report $report, Request $request)
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
        return [
            'uri' => $report->getExportLink($request)
        ];
    }

    /**
     * Download Report Results
     *
     * @param Report $report
     * @param Request $request
     * @return mixed
     * @throws UnknownExportDriverException
     */
    public function export(Report $report, Request $request)
    {
        if (auth()->guest()) {
            Auth::loginUsingId($request->get('uid'));
        }

        // Check that request has a valid url signature
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        return (new ExportReportService($report, $request))->handle();
    }

    /**
     * View the report preview
     *
     * @param Report $report
     * @param Request $request
     * @return mixed
     */
    public function preview(Report $report, Request $request)
    {
        $service = new BuildReportService($report, $request->toArray(), false);

        return $service->renderPreview(config('report.preview_limit'));
    }

}