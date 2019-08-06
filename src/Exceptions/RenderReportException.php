<?php

namespace MBLSolutions\Report\Exceptions;

use Exception;
use MBLSolutions\Report\Services\RenderReport;
use Throwable;

class RenderReportException extends Exception
{
    /** @var RenderReport $service */
    protected $service;

    /**
     * Report Render Exception
     * @param RenderReport $service
     * @param string $message
     * @param $code
     * @param Throwable|null $previous
     */
    public function __construct(RenderReport $service, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->service = $service;

        parent::__construct($message, null, $previous);

        $this->code = $code;
    }

    /**
     * Get the Report Service
     *
     * @return RenderReport
     */
    public function getService(): RenderReport
    {
        return $this->service;
    }

}