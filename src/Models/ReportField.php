<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportField extends Model
{
    use SoftDeletes;

    /** {@inheritDoc} */
    protected $guarded = [
        'id',
        'options'
    ];

    /**
     * Get the Report the fields belong to
     *
     * @return BelongsTo
     * @codeCoverageIgnore
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}