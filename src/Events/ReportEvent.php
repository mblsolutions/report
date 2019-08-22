<?php

namespace MBLSolutions\Report\Events;

use MBLSolutions\Report\Models\Report;

abstract class ReportEvent
{
    /** @var Report $report */
    public $report;

    /**
     * Create a new Report Event instance
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

}