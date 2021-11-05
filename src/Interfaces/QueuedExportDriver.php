<?php

namespace MBLSolutions\Report\Interfaces;

interface QueuedExportDriver
{

    /**
     * Store the Export as
     *
     * @param string $path
     * @param string $filesystem
     * @return bool
     */
    public function storeExportAs(string $path, string $filesystem): bool;

}