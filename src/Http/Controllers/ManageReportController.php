<?php

namespace MBLSolutions\Report\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MBLSolutions\Report\Exceptions\RenderReportException;
use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Services\TestReportService;

class ManageReportController
{
    /** @var ManageReportRepository $repository */
    protected $repository;

    /**
     * Manage Report Controller
     */
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
        return new ReportResource($this->repository->findOrNew($id));
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
        $validator = $this->repository->validateReportRequest($request);

        if ($validator->fails()) {
            return new Response([
                'message' => 'An error occurred',
                'errors' => $validator->errors()
            ], 422, [
                'Content-Type' => 'application/json',
            ]);
        }

        return new ReportResource($this->repository->create($request));
    }

    /**
     * Update a Report
     *
     * @param Report $report
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function update(Report $report, Request $request)
    {
        $validator = $this->repository->validateReportRequest($request);

        if ($validator->fails()) {
            return new Response([
                'message' => 'An error occurred',
                'errors' => $validator->errors()
            ], 422, [
                'Content-Type' => 'application/json',
            ]);
        }

        return new ReportResource($this->repository->update($report, $request));
    }

    /**
     * Delete a Report
     *
     * @param Report $report
     * @return ReportResource
     * @throws Exception
     */
    public function destroy(Report $report): ReportResource
    {
        return new ReportResource($this->repository->delete($report));
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

            $service = new TestReportService($request);

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