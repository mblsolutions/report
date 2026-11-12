<?php

namespace MBLSolutions\Report\Http\Controllers;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $this->abortUnlessAllowedToEdit();

        $request->validate($this->rules());

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
        abort_unless($this->repository->isOwnerOrAdmin($schedule), 404);

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
        $this->abortUnlessAllowedToEdit();

        $request->validate($this->rules());

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
        abort_unless($this->repository->isOwnerOrAdmin($schedule), 404);

        $schedule->delete();

        return new JsonResponse(null, 204);
    }

    /**
     * Create and update rules
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'report_id' => ['required', Rule::exists('reports', 'id')->withoutTrashed()],
            'parameters' => 'required|array',
            'frequency' => ['required', Rule::in(ReportSchedule::all()->values()->all())],
            'limit' => 'nullable|integer',
            'recipients' => ['bail', 'nullable', 'string', fn ($attribute, $value, $fail) => $this->checkRecipients($value, $fail)],
            'parameters.export_driver' => ['sometimes', Rule::in(config('report.queued_export_drivers', []))],
        ];
    }

    /**
     * Comma separated emails, on schedules.recipient_domains when it is set
     *
     * @param string $value
     * @param Closure $fail
     * @return void
     */
    protected function checkRecipients(string $value, Closure $fail): void
    {
        $domains = config('report.schedules.recipient_domains', []);
        $domains = array_filter(array_map(
            static fn ($domain) => strtolower(trim($domain)),
            is_string($domains) ? explode(',', $domains) : (array) $domains
        ));

        foreach (array_filter(array_map('trim', explode(',', $value)), static fn ($email) => $email !== '') as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fail('Each recipient must be an email address.');

                return;
            }

            if ($domains && ! in_array(strtolower(substr(strrchr($email, '@'), 1)), $domains, true)) {
                $fail('Each recipient must use an allowed email domain.');

                return;
            }
        }
    }

    /**
     * Only admins can create or edit schedules, unless schedules.admin_only is false
     *
     * @return void
     */
    protected function abortUnlessAllowedToEdit(): void
    {
        abort_if(config('report.schedules.admin_only', true) && ! $this->repository->signedInUserIsAdmin(), 403);
    }

    /**
     * Non-admins can't schedule admin only reports
     *
     * @param Request $request
     * @return void
     */
    protected function abortIfReportHidden(Request $request): void
    {
        // Same source as only(): the body wins over the query string
        $report = Report::find($request->input('report_id'));

        abort_if($report && app(ReportRepository::class)->isHiddenFromUser($report), 404);
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