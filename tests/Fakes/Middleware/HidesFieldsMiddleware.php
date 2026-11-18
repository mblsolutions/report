<?php

namespace MBLSolutions\Report\Tests\Fakes\Middleware;

use MBLSolutions\Report\Models\ReportField;

/** Scopes like FakeMiddleware and hides every field from the runner */
class HidesFieldsMiddleware extends FakeMiddleware
{

    public function field(ReportField $field): bool
    {
        return false;
    }

}
