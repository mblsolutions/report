<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Repositories\ReportJobRepository;
use MBLSolutions\Report\Services\QueuedReportExportService;
use MBLSolutions\Report\Support\Report\RenderJobUuidGenerator;

class QueuedReportController
{
    /** @var ReportJobRepository $repository */
    protected ReportJobRepository $repository;

    /**
     * Report Controller
     *
     * @param ReportJobRepository $repository
     */
    public function __construct(ReportJobRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * View Report Job Index
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->repository->paginate($request->get('limit', config('app.pagination_limit'))),
            200
        );
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

    /**
     * Generate Export links for Report Job
     *
     * @param ReportJob $job
     * @return JsonResponse
     */
    public function export(ReportJob $job): JsonResponse
    {
        $service = new QueuedReportExportService($job);

        return new JsonResponse($service->getExportResponse(), 200);
    }

}