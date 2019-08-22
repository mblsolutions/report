<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportConnection;

class ConnectionController
{
    /** @var ReportConnection $connection */
    protected $connection;

    /**
     * Connection Controller
     *
     * @param ReportConnection $connection
     */
    public function __construct(ReportConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get a list of available connections
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return $this->connection->all();
    }

}