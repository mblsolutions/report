<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;

class SelectFieldModel
{
    /** @var Collection $models */
    protected $models;

    /**
     * Select Field Models
     */
    public function __construct()
    {
        $this->models = collect(config('report.models'));
    }

    /**
     * Get all Select Field Models
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

    /**
     * Format Class Name
     *
     * @param string $namespace
     * @return mixed
     */
    public function formatClassName(string $namespace)
    {
        $parts = explode('\\', $namespace);

        return end($parts);
    }

}