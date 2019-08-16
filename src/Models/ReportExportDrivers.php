<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Interfaces\ExportDriver;
use MBLSolutions\Report\Support\ConfigModel;

class ReportExportDrivers extends ConfigModel
{
    /** {@inheritDoc} */
    protected $key = 'report.export_drivers';

    /**
     * Get all Config Models
     *
     * @return Collection
     */
    public function all(): Collection
    {
        $models = $this->models->map(function ($namespace) {
            return [
                'value' => $this->hashDriverValue($namespace),
                'name' => $this->formatClassName($namespace)
            ];
        });

        return $models->sortBy('name');
    }

    /**
     * Find driver by its hash
     *
     * @param string $hash
     * @return ExportDriver
     */
    public function findByHash(string $hash): ExportDriver
    {
        $driver = $this->models->reject(function ($driver) use ($hash) {
            return $this->hashDriverValue($driver) !== $hash;
        });

        $namespace = $driver->first();

        return new $namespace;
    }

    /**
     * Has the driver value
     *
     * @param string $driver
     * @return string
     */
    private function hashDriverValue(string $driver): string
    {
        return hash('sha256', $driver);
    }

}