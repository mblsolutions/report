<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MBLSolutions\Report\Http\Resources\ScheduledReportCollection;
use MBLSolutions\Report\Http\Resources\ScheduledReportResource;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;

class ScheduledReportController
{
    /** @var ScheduledReportRepository $repository */
    protected ScheduledReportRepository $repository;

    /**
     * Report Controller
     *
     * @param ScheduledReportRepository $repository
     */
    public function __construct(ScheduledReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): ScheduledReportCollection
    {
        return new ScheduledReportCollection(
            $this->repository->paginate($request->get('limit', config('app.pagination_limit')))
        );
    }

    public function create(Request $request): ScheduledReportResource
    {
        $schedule = new ScheduledReport(array_merge([
            'uuid' => Str::uuid()
        ], $request->toArray()));


        $schedule->save();

        return new ScheduledReportResource($schedule);
    }

    /**
     * Delete a Scheduled Report
     *
     * @param ScheduledReport $schedule
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(ScheduledReport $schedule): JsonResponse
    {
        $schedule->delete();

        return new JsonResponse(null, 204);
    }

}