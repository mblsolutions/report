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
                'name' => $this->formatClassName($namespace)
            ];
        });

        return $models->sortBy('name');
    }

}