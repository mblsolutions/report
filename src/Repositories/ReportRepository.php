<?php

namespace MBLSolutions\Report\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Models\Report;

class ReportRepository extends ManageReportRepository
{

    /**
     * All Reports
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginate($limit = 25): LengthAwarePaginator
    {
        return Report::paginate($limit);
    }

    /**
     * Get a Report
     *
     * @param $id
     * @return Report
     */
    public function find($id): Report
    {
        return Report::findOrFail($id);
    }

    /**
     * Create a new Report
     *
     * @param Request $request
     * @return Report
     * @throws Exception
     */
    public function create(Request $request): Report
    {
        try {
            DB::beginTransaction();

            /** @var Report $report */
            $report = Report::create($request->except('fields', 'selects', 'joins'));

            $this->handleReportRelations($report, $request);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report->fresh();
    }


    /**
     * Update a Report
     *
     * @param Report $report
     * @param Request $request
     * @return Report
     * @throws Exception
     */
    public function update(Report $report, Request $request): Report
    {
        try {
            DB::beginTransaction();

            /** @var Report $report */
            $report->update($request->except('fields', 'selects', 'joins'));

            $this->handleReportRelations($report, $request);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report->fresh();
    }


}