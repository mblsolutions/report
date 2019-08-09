<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Support\ConfigModel;

class ReportConnection extends ConfigModel
{
    /** {@inheritDoc} */
    protected $key = 'database.connections';

    /**
     * Get all Select Field Models
     *
     * @return Collection
     */
    public function all(): Collection
    {
        $connections = $this->models->map(function (array $connection, string $key) {
            return [
                'value' => $key,
                'name' => $this->formatName($key),
                'default' => $this->isPrimary($key)
            ];
        });

        return $connections->sortBy('name');
    }

    /**
     * Is connection the primary
     *
     * @param string $key
     * @return bool
     */
    public function isPrimary(string $key): bool
    {
        return config('database.default') === $key;
    }

    /**
     * Format Class Name
     *
     * @param string $key
     * @return mixed
     */
    public function formatName(string $key)
    {
        $parts = array_map('ucfirst', explode('_', $key));

        $name = implode('', $parts);

        if ($this->isPrimary($key)) {
            return $name . ' (default)';
        }

        return $name;
    }

}