<?php

namespace MBLSolutions\Report\Events;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;

class ReportChunkComplete extends ReportEvent
{

    public ReportJob $job;

    /**
     * Create a new Report Event instance
     *
     * @param Report $report
     * @param ReportJob $job
     */
    public function __construct(Report $report, ReportJob $job)
    {
        $this->job = $job;

        parent::__construct($report);
    }

}