<?php

namespace MBLSolutions\Report\Repositories;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;

class ManageReportRepository
{

    /**
     * Create a new Report
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function create(Request $request)
    {
        $this->validateReportRequest($request);

        try {
            DB::beginTransaction();

            /** @var Report $report */
            $report = Report::create($request->except('fields', 'selects', 'joins'));

            if ($request->get('fields')) {
                $report->fields()->createMany($request->get('fields'));
            }

            if ($request->get('selects')) {
                $report->selects()->createMany($request->get('selects'));
            }

            if ($request->get('joins')) {
                $report->joins()->createMany($request->get('joins'));
            }

            DB::commit();

            return new ReportResource($report->fresh());
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
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
        $this->validateReportRequest($request);

        try {
            DB::beginTransaction();

            /** @var Report $report */
            $report->update($request->except('fields', 'selects', 'joins'));

            if ($request->get('fields')) {
                $report->fields()->updateOrCreate($request->get('fields'));
            }

            if ($request->get('selects')) {
                $report->selects()->updateOrCreate($request->get('selects'));
            }

            if ($request->get('joins')) {
                $report->joins()->updateOrCreate($request->get('joins'));
            }

            DB::commit();

            return new ReportResource($report->fresh());
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Validate Report Request
     *
     * @param Request $request
     * @return bool|Response
     */
    public function validateReportRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return new Response($validator->errors(), 422, [
                'Content-Type' => 'application/json',
            ]);
        }

        return true;
    }

}