<?php

namespace MBLSolutions\Report\Models;

use MBLSolutions\Report\Support\EnumModel;

class ReportJoinType extends EnumModel
{
    /** @var string */
    public const INNER_JOIN = 'inner_join';

    /** @var string */
    public const LEFT_JOIN = 'left_join';

    /** @var string */
    public const RIGHT_JOIN = 'right_join';
}