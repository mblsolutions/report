<?php

namespace MBLSolutions\Report\Models;

use MBLSolutions\Report\Support\EnumModel;

class ReportFieldType extends EnumModel
{
    /** @var string */
    public const TEXT = 'text';

    /** @var string */
    public const NUMBER = 'number';

    /** @var string */
    public const DATE = 'date';

    /** @var string */
    public const TIME = 'time';

    /** @var string */
    public const DATETIME = 'datetime';

    /** @var string */
    public const SELECT = 'select';
}