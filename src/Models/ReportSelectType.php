<?php

namespace MBLSolutions\Report\Models;

use MBLSolutions\Report\Support\EnumModel;

class ReportSelectType extends EnumModel
{
    /** @var string */
    public const STRING = 'string';

    /** @var string */
    public const STRING_COUNT = 'string_count';

    /** @var string */
    public const INTEGER = 'integer';

    /** @var string */
    public const INTEGER_SUM = 'integer_sum';

    /** @var string */
    public const INTEGER_COUNT = 'integer_count';

    /** @var string */
    public const DECIMAL = 'decimal';

    /** @var string */
    public const DECIMAL_SUM = 'decimal_sum';

    /** @var string */
    public const CURRENCY = 'currency';

    /** @var string */
    public const CURRENCY_SUM = 'currency_sum';

    /** @var string */
    public const DATE = 'date';

    /** @var string */
    public const DATETIME = 'datetime';
}