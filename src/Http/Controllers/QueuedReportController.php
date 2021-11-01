<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Repositories\ReportRepository;
use MBLSolutions\Report\Services\QueuedReportExportService;
use MBLSolutions\Report\Support\Report\RenderJobUuidGenerator;

class QueuedReportController
{
    /** @var ReportRepository $repository */
    protected ReportRepository $repository;

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
     * View Report Job Index
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(ReportJob::orderByDesc('updated_at')->paginate(), 200);
    }

    /**
     * Render a Report
     *
     * @param Report $report
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Report $report, Request $request): JsonResponse
    {
        $data = app(RenderJobUuidGenerator::class)->__invoke();

        Bus::dispatch(new RenderReport($data->get('uuid'), $report, $request->toArray()));

        return new JsonResponse($data, 202);
    }

    /**
     * View Report Job Status
     *
     * @param ReportJob $job
     * @return JsonResponse
     */
    public function job(ReportJob $job): JsonResponse
    {
        return new JsonResponse($job, 200);
    }

    public function export(ReportJob $job): JsonResponse
    {
        $service = new QueuedReportExportService($job);

        return new JsonResponse($service->getExportResponse(), 200);
    }

}