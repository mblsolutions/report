<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Support\ConfigModel;

class ReportMiddlewareOptions extends ConfigModel
{
    /** {@inheritDoc} */
    protected $key = 'report.middleware';

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
        $name = parent::formatName($namespace);

        return implode(' ', preg_split('/(?=[A-Z])/', $name));

    }

}