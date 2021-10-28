<?php

namespace MBLSolutions\Report\Support\Enums;

use MBLSolutions\Report\Support\EnumModel;

class JobStatus extends EnumModel
{
    public const SCHEDULED = 'scheduled';

    public const RUNNING = 'running';

    public const COMPLETE = 'complete';

    public const FAILED = 'failed';

}