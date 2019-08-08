<?php

namespace MBLSolutions\Report\Exceptions;

use Exception;
use MBLSolutions\Report\Services\BuildReportService;
use Throwable;

class RenderReportException extends Exception
{
    /** @var BuildReportService $service */
    protected $service;

    /**
     * Report Render Exception
     * @param BuildReportService $service
     * @param string $message
     * @param $code
     * @param Throwable|null $previous
     */
    public function __construct(BuildReportService $service, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->service = $service;

        parent::__construct($message, null, $previous);

        $this->code = $code;
    }

    /**
     * Get the Report Service
     *
     * @return BuildReportService
     */
    public function getService(): BuildReportService
    {
        return $this->service;
    }

}