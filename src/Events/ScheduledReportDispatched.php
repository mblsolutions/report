<?php

namespace MBLSolutions\Report\Events;

use MBLSolutions\Report\Models\ScheduledReport;

class ScheduledReportDispatched
{

    public ScheduledReport $schedule;

    public function __construct(ScheduledReport $schedule)
    {
        $this->schedule = $schedule;
    }

}