<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    /** {@inheritDoc} */
    protected $guarded = [];

    protected $casts = [
        'show_data' => 'boolean',
        'show_totals' => 'boolean',
        'active' => 'boolean'
    ];

    /**
     * Get the fields that belong to the report
     *
     * @return HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ReportField::class);
    }

    /**
     * Get the selects that belong to the report
     *
     * @return HasMany
     */
    public function selects(): HasMany
    {
        return $this->hasMany(ReportSelect::class);
    }

    /**
     * Get the joins that belong to the report
     *
     * @return HasMany
     */
    public function joins(): HasMany
    {
        return $this->hasMany(ReportJoin::class);
    }

}