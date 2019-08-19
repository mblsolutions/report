<?php

namespace MBLSolutions\Report\Repositories;

use Exception;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportDataType;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportMiddlewareOptions;
use MBLSolutions\Report\Models\ReportSelectField;

class ManageReportRepository
{


    /**
     * Get a Report or make a New One
     *
     * @param null $id
     * @return Report
     */
    public function findOrNew($id = null): Report
    {
        if ($id !== 'null' && $id !== null) {
            $report = Report::findOrFail($id);
        }

        return $report ?? new Report;
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
            $report = Report::create($request->toArray());

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
            $report->update($request->toArray());

            $this->handleReportRelations($report, $request);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report->fresh();
    }

    /**
     * Delete a Report
     *
     * @param Report $report
     * @return Report
     * @throws Exception
     */
    public function delete(Report $report): Report
    {
        $report->delete();

        return $report->fresh();
    }

    /**
     * Update or Create Report Relations
     *
     * @param Report $report
     * @param Request $request
     * @return ManageReportRepository
     */
    public function handleReportRelations(Report $report, Request $request): ManageReportRepository
    {
        if ($request->get('fields')) {
            $this->updateOrCreateFields($report, collect($request->get('fields')));
        }

        if ($request->get('selects')) {
            $this->updateOrCreateSelects($report, collect($request->get('selects')));
        }

        if ($request->get('joins')) {
            $this->updateOrCreateJoins($report, collect($request->get('joins')));
        }

        if ($request->get('middleware')) {
            $this->updateOrCreateMiddleware($report, collect($request->get('middleware')));
        }

        return $this;
    }

    /**
     * Update/Create Report Fields
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateFields(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->fields()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Update/Create Report Selects
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateSelects(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->selects()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Update/Create Report Joins
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateJoins(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->joins()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Update/Create Report Middleware
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateMiddleware(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->middleware()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Validate Report Request
     *
     * @param Request $request
     * @return ValidatorContract
     * @throws \ReflectionException
     */
    public function validateReportRequest(Request $request): ValidatorContract
    {
        return Validator::make($request->all(), $this->getRules(), $this->getMessages());
    }

    /**
     * Get the Validation Rules
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'connection' => 'required|string|max:100',
            'display_limit' => 'required|numeric|between:25,500',
            'table' => 'required|string|max:128',
            'where' => 'nullable',
            'groupby' => 'nullable',
            'having' => 'nullable',
            'orderby' => 'nullable',
            'show_data' => 'required|boolean',
            'show_totals' => 'required|boolean',
            'active' => 'required|boolean',
            // Middleware Validation
            'middleware.*.middleware' => [
                'required',
                Rule::in((new ReportMiddlewareOptions)->all()->pluck('value')->toArray())
            ],
            // Fields Validation
            'fields.*.label' => 'required|string|max:100',
            'fields.*.alias' => 'required|string',
            'fields.*.type' => [
                'required',
                Rule::in(ReportFieldType::all()->toArray())
            ],
            'fields.*.model' => [
                'required_if:fields.*.type,select',
                Rule::in(array_merge([null], (new ReportSelectField)->all()->pluck('value')->toArray()))
            ],
            'fields.*.model_select_name' => 'required_if:fields.*.type,select',
            'fields.*.model_select_value' => 'required_if:fields.*.type,select',
            // Selects Validation
            'selects.*.column' => 'required|string',
            'selects.*.alias' => 'required|string|max:50',
            'selects.*.type' => [
                'required',
                Rule::in((new ReportDataType)->all()->pluck('value')->toArray())
            ],
            'selects.*.column_order' => 'required|integer',
            // Joins Validation
            'joins.*.type' => [
                'required',
                Rule::in(['inner_join', 'left_join', 'right_join'])
            ],
            'joins.*.table' => 'required|string|max:128',
            'joins.*.first' => 'required|string|max:128',
            'joins.*.operator' => 'required|string|max:2',
            'joins.*.second' => 'required|string|max:128',
        ];
    }

    /**
     * Get the Validation Messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return [
            // Middleware Validation Messages
            'middleware.*.middleware.required' => 'Middleware is required.',
            'middleware.*.middleware.in' => 'Middleware is not valid.',

            // Fields Validation Messages
            'fields.*.label.required' => 'The field label is required.',
            'fields.*.label.string' => 'The field label must be a string.',
            'fields.*.label.max' => 'The field label length must not exceed 100 characters.',

            'fields.*.alias.required' => 'The field alias is required.',

            'fields.*.type.required' => 'The field type is required.',
            'fields.*.type.in' => 'The field model is not valid.',

            'fields.*.model.required_if' => 'The field model is required if type is select.',
            'fields.*.model_select_name.required_if' => 'The field select name is required if type is select.',
            'fields.*.model_select_value.required_if' => 'The field select value is required if type is select.',

            // Selects Validation Messages
            'selects.*.column.required' => 'The select column is required.',
            'selects.*.column.string' => 'The select column must be a string.',

            'selects.*.alias.required' => 'The select column alias is required.',
            'selects.*.alias.string' => 'The select column alias must be a string.',
            'selects.*.alias.max' => 'The select column alias length must not exceed 50 characters..',

            'selects.*.type.required' => 'The select column type is required.',
            'selects.*.type.in' => 'The select column type is not valid.',

            'selects.*.column_order.required' => 'The select column order is required.',
            'selects.*.column_order.integer' => 'The select column order must be an integer.',

            // Joins Validation Messages
            'joins.*.type.required' => 'The join type is required.',
            'joins.*.type.in' => 'The join type is not valid.',

            'joins.*.table.required' => 'The join table is required.',
            'joins.*.table.string' => 'The join table must be a string.',
            'joins.*.table.max' => 'The join table length must not exceed 128 characters.',

            'joins.*.first.required' => 'The join first column is required.',
            'joins.*.first.string' => 'The join first column must be a string.',
            'joins.*.first.max' => 'The join first column must not exceed 128 characters.',

            'joins.*.operator.required' => 'The join operator is required.',
            'joins.*.operator.string' => 'The join operator must be a string.',
            'joins.*.operator.max' => 'The join table operator must not exceed 2 characters.',

            'joins.*.second.required' => 'The join second column is required.',
            'joins.*.second.string' => 'The join second column must be a string.',
            'joins.*.second.max' => 'The join second column must not exceed 128 characters.',

        ];
    }

}