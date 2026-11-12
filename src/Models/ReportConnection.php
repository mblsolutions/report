<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Support\ConfigModel;

class ReportConnection extends ConfigModel
{
    /** {@inheritDoc} */
    protected string $key = 'database.connections';

    /**
     * Get all Select Field Models
     *
     * @return Collection
     */
    public function all(): Collection
    {
        $connections = $this->models->only(static::allowed())->map(function (array $connection, string $key) {
            return [
                'value' => $key,
                'name' => $this->formatName($key),
                'default' => $this->isPrimary($key)
            ];
        });

        return $connections->sortBy('name');
    }

    /**
     * Connections reports may use: report.allowed_connections, else the default connection
     *
     * @return array
     */
    public static function allowed(): array
    {
        $allowed = config('report.allowed_connections');

        if (is_string($allowed)) {
            $allowed = explode(',', $allowed);
        }

        $allowed = array_values(array_filter(array_map('trim', (array) $allowed)));

        return $allowed ?: [config('database.default')];
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