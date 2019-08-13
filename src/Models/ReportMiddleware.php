<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportMiddleware extends Model
{
    use SoftDeletes;

    /** {@inheritDoc} */
    protected $table = 'report_middleware';

    /** {@inheritDoc} */
    protected $guarded = [
    ];

    /**
     * Get the Report the middleware belongs to
     *
     * @return BelongsTo
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

}