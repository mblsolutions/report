<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Exceptions\UnknownExportDriverException;
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
     * @throws UnknownExportDriverException
     */
    public function findByHash(string $hash): ExportDriver
    {
        $driver = $this->models->reject(static function ($driver) use ($hash) {
            return self::hashDriverValue($driver) !== $hash;
        });

        $namespace = $driver->first();

        if (!class_exists($namespace)) {
            throw new UnknownExportDriverException('Supplied driver could not be found.');
        }

        return new $namespace;
    }

    /**
     * Has the driver value
     *
     * @param string $driver
     * @return string
     */
    public static function hashDriverValue(string $driver): string
    {
        return hash('sha256', $driver);
    }

}