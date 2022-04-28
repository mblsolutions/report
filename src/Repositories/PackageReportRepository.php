<?php

namespace MBLSolutions\Report\Repositories;

use Lerouse\LaravelRepository\EloquentRepository;
use MBLSolutions\Report\Support\HandlesAuthenticatable;

abstract class PackageReportRepository extends EloquentRepository
{
    use HandlesAuthenticatable;

}