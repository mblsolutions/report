<?php

namespace MBLSolutions\Report\Events;

use MBLSolutions\Report\Models\Report;

abstract class ReportEvent
{
    /** @var Report $report */
    public Report $report;

    /** @var mixed|null Who ran the report, if known */
    public $authenticatableId = null;

    /** Request parameters, without the export link keys */
    public array $parameters = [];

    /** Rows rendered or exported, if counted */
    public ?int $rows = null;

    /**
     * Create a new Report Event instance
     *
     * @param Report $report
     * @param mixed|null $authenticatableId
     * @param array $parameters
     * @param int|null $rows
     */
    public function __construct(Report $report, $authenticatableId = null, array $parameters = [], ?int $rows = null)
    {
        $this->report = $report;
        $this->authenticatableId = $authenticatableId;
        $this->parameters = $parameters;
        $this->rows = $rows;
    }

}
