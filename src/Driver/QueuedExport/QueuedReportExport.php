<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use MBLSolutions\Report\Interfaces\QueuedExportDriver;
use MBLSolutions\Report\Services\BuildReportService;

abstract class QueuedReportExport implements QueuedExportDriver
{
    protected ?BuildReportService $service = null;

    protected int $offset;

    protected int $limit;

    /**
     * Queued Report Export
     *
     * @param BuildReportService|null $service
     * @param int $offset
     * @param int $limit
     */
    public function __construct(BuildReportService $service = null, int $offset = 0, int $limit = 1000)
    {
        $this->service = $service;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * Get Export Driver Name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        if ($this->name) {
            return $this->name;
        }

        return null;
    }

}