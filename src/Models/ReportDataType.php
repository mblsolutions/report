<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Support\ConfigModel;

class ReportDataType extends ConfigModel
{
    /** {@inheritDoc} */
    protected $key = 'report.data_types';

    /**
     * Get all Config Models
     *
     * @return Collection
     */
    public function all(): Collection
    {
        $models = $this->models->map(function ($namespace) {
            return [
                'value' => $namespace,
                'name' => $this->formatName($namespace)
            ];
        });

        return $models->sortBy('name');
    }

    /**
     * Format Name
     *
     * @param string $namespace
     * @return string
     */
    public function formatName(string $namespace): string
    {
        if (class_exists($namespace)) {
            $type = new $namespace;

            if (property_exists($type, 'name')) {
                return $type->name;
            }
        }

        return parent::formatName($namespace);
    }

}