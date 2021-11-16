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
        $this->models = new Collection(config($this->key));
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

    /**
     * Format Class Name
     *
     * @param string $namespace
     * @return string
     */
    public function formatClassName(string $namespace): string
    {
        if (class_exists($namespace)) {
            $type = new $namespace;

            if (property_exists($type, 'name')) {
                return $type->name;
            }
        }

        $name = $this->formatName($namespace);

        return trim(implode(' ', preg_split('/(?=[A-Z])/', $name)));

    }

}