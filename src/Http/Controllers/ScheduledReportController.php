<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MBLSolutions\Report\Http\Resources\ScheduledReportCollection;
use MBLSolutions\Report\Http\Resources\ScheduledReportResource;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

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

    /**
     * Create a Scheduled Report
     *
     * @param Request $request
     * @return ScheduledReportResource
     */
    public function create(Request $request): ScheduledReportResource
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'required',
            'frequency' => 'required',
            'limit' => 'nullable|integer',
        ]);

        $schedule = new ScheduledReport(array_merge([
            'uuid' => Str::uuid(),
            'authenticatable_id' => optional(Auth::user())->getKey(),
        ], $request->toArray()));

        $schedule->save();

        return new ScheduledReportResource($schedule);
    }

    /**
     * Show Scheduled Report
     *
     * @param ScheduledReport $schedule
     * @return ScheduledReportResource
     */
    public function show(ScheduledReport $schedule): ScheduledReportResource
    {
        return new ScheduledReportResource($schedule);
    }

    /**
     * Update Scheduled Report
     *
     * @param ScheduledReport $schedule
     * @param Request $request
     * @return ScheduledReportResource
     */
    public function update(ScheduledReport $schedule, Request $request): ScheduledReportResource
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'parameters' => 'required',
            'frequency' => 'required',
            'limit' => 'nullable|integer',
        ]);

        return new ScheduledReportResource(
            $this->repository->update($request->toArray(), $schedule->getKey())
        );
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

    /**
     * Get the report Run frequencies
     *
     * @return Collection
     */
    public function frequencies(): Collection
    {
        return ReportSchedule::options();
    }

}