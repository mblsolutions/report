<?php

namespace MBLSolutions\Report\Interfaces;

use Illuminate\Support\Collection;

interface PopulatesReportOption
{

    /**
     * Collection of Report Options
     *
     * @param string $value
     * @param string $name
     * @return Collection
     */
    public static function options(string $value = 'id', string $name = 'name'): Collection;

}