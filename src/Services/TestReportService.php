<?php

namespace MBLSolutions\Report\Services;

use Exception;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Exceptions\RenderReportException;

class TestReportService extends BuildReportService
{

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