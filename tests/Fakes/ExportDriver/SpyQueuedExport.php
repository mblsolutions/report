<?php

namespace MBLSolutions\Report\Tests\Fakes\ExportDriver;

/** Not in queued_export_drivers; counts how often it is built */
class SpyQueuedExport
{
    public static int $constructed = 0;

    public function __construct()
    {
        static::$constructed++;
    }

    public function getName(): string
    {
        return 'Spy';
    }
}
