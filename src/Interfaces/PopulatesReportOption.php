<?php

namespace MBLSolutions\Report\Interfaces;

use Illuminate\Support\Collection;

interface PopulatesReportOption
{

    /**
     * Collection of Report Options
     *
     * @return Collection
     */
    public function options(): Collection;

}