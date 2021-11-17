<?php

namespace MBLSolutions\Report\Support\Enums;

use MBLSolutions\Report\Support\EnumModel;

class ReportSchedule extends EnumModel
{
    public const DAILY = 'daily';

    public const WEEKLY = 'weekly';

    public const MONTHLY = 'monthly';

    public const QUARTERLY = 'quarterly';

    public const YEARLY = 'yearly';

}