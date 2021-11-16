<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JsonException;

class ReportJob extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    /** {@inheritDoc} */
    protected $guarded = [];

    protected $casts = [
        'parameters' => 'array',
        'formatted_parameters' => 'array',
        'processed' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Belongs to a Report
     *
     * @return BelongsTo
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

}