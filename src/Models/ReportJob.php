<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportJob extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    /** {@inheritDoc} */
    protected $guarded = [];


    protected $casts = [
        'processed' => 'integer',
        'total' => 'integer',
    ];

}