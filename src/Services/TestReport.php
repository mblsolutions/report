<?php

namespace MBLSolutions\Report\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Exceptions\RenderReportException;
use MBLSolutions\Report\Models\Report;

class TestReport extends RenderReport
{

    /**
     * Create a new Test Report Service Instance
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $report = new Report($request->except('selects', 'joins', 'fields'));

        parent::__construct($report);
    }

    /**
     * Run the Report
     *
     * @return Collection
     * @throws RenderReportException
     */
    public function run(): Collection
    {
        try {
            return $this->render();
        } catch (Exception $exception) {
            throw new RenderReportException($this, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }

    }
    
}