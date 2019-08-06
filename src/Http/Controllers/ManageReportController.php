<?php

namespace MBLSolutions\Report\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use MBLSolutions\Report\Exceptions\RenderReportException;
use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Services\TestReport;

class ManageReportController
{
    /** @var ManageReportRepository $repository */
    protected $repository;

    public function __construct()
    {
        $this->repository = new ManageReportRepository;
    }

    /**
     * Show a Report
     *
     * @param int|null $id
     * @return ReportResource
     */
    public function show($id = null): ReportResource
    {
        if ($id !== 'null' && $id !== null) {
            $report = Report::findOrFail($id);
        }

        return new ReportResource($report ?? new Report());
    }

    /**
     * Create a Report
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function store(Request $request)
    {
        return $this->repository->create($request);
    }

    public function update(Report $report, Request $request): array
    {
        return $this->repository->update($request);
    }

    public function destroy(): array
    {
        // TODO implement method

        return [];
    }

    /**
     * Test Report Config
     *
     * @param Request $request
     * @return mixed
     */
    public function test(Request $request)
    {
        try {
            $start = microtime(true);

            $service = new TestReport($request);

            return [
                'success' => true,
                'results' => $service->run(),
                'request' => $request->all(),
                'time' => number_format(microtime(true) - $start, 6)
            ];
        } catch (RenderReportException $exception) {
            return [
                'success' => false,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'raw' => $exception->getService()->getRawQuery()
            ];
        }
    }


}