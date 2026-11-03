<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MBLSolutions\Report\GenerateReportExportUri;

class Report extends Model
{
    use SoftDeletes;

    /** {@inheritDoc} */
    protected $guarded = [
        'id',
        'fields',
        'selects',
        'joins',
        'middleware'
    ];

    protected $casts = [
        'display_limit' => 'integer',
        'show_data' => 'boolean',
        'show_totals' => 'boolean',
        'admin_only' => 'boolean',
        'active' => 'boolean',
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
        return $this->hasMany(ReportSelect::class)->orderBy('column_order');
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

    /**
     * Get the middleware that belong to the report
     *
     * @return HasMany
     */
    public function middleware(): HasMany
    {
        return $this->hasMany(ReportMiddleware::class);
    }

    /**
     * Should Data be shown on Report
     *
     * @return bool
     */
    public function shouldShowData(): bool
    {
        return $this->show_data;
    }

    /**
     * Should Totals be shown on Report
     *
     * @return bool
     */
    public function shouldShowTotals(): bool
    {
        return $this->show_totals;
    }

    /**
     * Generate an Export URI
     *
     * @param Request $request
     * @return string
     */
    public function getExportLink(Request $request): string
    {
        // Keep plain named keys only. Numeric keys go into the URL as raw text,
        // and keys PHP reads back as uid/expires (e.g. "uid[0]") could replace them.
        $params = array_filter($request->except('signature'), static function ($key) {
            return is_string($key) && !preg_match('/[\[\0]/', $key) && !in_array(
                str_replace([' ', '.'], '_', ltrim($key, ' ')), ['report', 'uid', 'expires', 'signature'], true
            );
        }, ARRAY_FILTER_USE_KEY);

        $uid = auth()->user()->id;

        // Server values win over the request
        $params = array_merge($params, [
            'report' => $this->id,
            'uid' => $uid
        ]);

        $link = app(GenerateReportExportUri::class)->__invoke($params);

        // Backstop: PHP must read back our uid and a single expires from the link
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

        if (($query['uid'] ?? null) !== ($uid === null ? null : (string) $uid) || !is_scalar($query['expires'] ?? '')) {
            throw new InvalidArgumentException('Export link parameters are not allowed.');
        }

        return $link;
    }

    public function getSlug(): string
    {
        $name = $this->getAttribute('name');

        return Str::slug($name);
    }

}