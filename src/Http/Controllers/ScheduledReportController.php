<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MBLSolutions\Report\Http\Resources\ScheduledReportCollection;
use MBLSolutions\Report\Http\Resources\ScheduledReportResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Repositories\ReportRepository;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

class ScheduledReportController
{
    /** Fields a user may set; owner and uuid come from the server */
    private const FIELDS = ['report_id', 'parameters', 'frequency', 'limit', 'recipients'];

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

        $this->abortIfReportHidden($request);

        $schedule = new ScheduledReport(array_merge($request->only(self::FIELDS), [
            'uuid' => Str::uuid(),
            'authenticatable_id' => optional(Auth::user())->getKey(),
        ]));

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

        $this->abortIfReportHidden($request);

        return new ScheduledReportResource(
            $this->repository->update($request->only(self::FIELDS), $schedule->getKey())
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
     * Non-admins can't schedule admin only reports
     *
     * @param Request $request
     * @return void
     */
    protected function abortIfReportHidden(Request $request): void
    {
        abort_if(app(ReportRepository::class)->isHiddenFromUser(Report::findOrFail($request->get('report_id'))), 404);
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