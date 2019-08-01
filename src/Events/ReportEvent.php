<?php

namespace MBLSolutions\Report\Events;

use MBLSolutions\Report\Report;

abstract class ReportEvent
{
    /** @var Report $report */
    protected $report;

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