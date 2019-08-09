<?php

namespace MBLSolutions\Report\Support;

use Illuminate\Support\Collection;

abstract class ConfigModel
{
    /** @var string $key */
    protected $key;

    /** @var Collection $models */
    protected $models;

    /**
     * Config Model Instance
     */
    public function __construct()
    {
        $this->models = collect(config($this->key));
    }

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
     * Format Class Name
     *
     * @param string $namespace
     * @return mixed
     */
    public function formatName(string $namespace)
    {
        $parts = explode('\\', $namespace);

        return end($parts);
    }

}