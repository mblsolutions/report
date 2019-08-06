<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\SelectConnectionModel;

class ConnectionController
{

    /**
     * Get a list of available connections
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return (new SelectConnectionModel())->all();
    }

}