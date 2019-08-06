<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportSelect;

class RenderReport
{
    /** @var bool $paginate */
    public $paginate = true;

    /** @var int $limit */
    public $limit = 25;

    /** @var Report $report */
    protected $report;

    /** @var Collection $headings */
    protected $headings;

    /** @var Builder $query */
    protected $query;

    /**
     * Create a new Render Report Service Instance
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;

        $this->query = $this->buildReportQuery();
    }

    /**
     * Render the Report
     *
     * @return Collection
     */
    public function render(): Collection
    {
        return collect([
            'headings' => $this->headings(),
            'data' => $this->data(),
            'totals' => $this->totals(),
            'raw' => $this->getRawQuery()
        ]);
    }

    /**
     * Get the Raw Query
     *
     * @return string
     */
    public function getRawQuery(): string
    {
        return $this->query->toSql();
    }

    /**
     * @return Collection
     */
    public function headings(): Collection
    {
        if ($this->headings === null) {
            $this->headings = $this->report->selects()->orderBy('column_order', 'asc')->pluck('alias');
        }

        return $this->headings;
    }

    /**
     * Build Report Query
     *
     * @return Builder
     */
    protected function buildReportQuery(): Builder
    {
        $this->query = DB::connection($this->report->connection)->table($this->report->table);

        $this->report->selects->each(function (ReportSelect $select) {
            $this->query = $this->query->selectRaw("{$select->column} AS '{$select->alias}'");
        });

        return $this->query;
    }

    /**
     * Get Report Data
     *
     * @return mixed
     */
    private function data()
    {
        if ($this->report->shouldShowData()) {
            return $this->query->paginate($this->limit);
        }

        return false;
    }

    /**
     * Get Report Totals
     *
     * @return mixed
     */
    private function totals()
    {
        if ($this->report->shouldShowTotals()) {
            return [];
        }

        return false;
    }
    
}