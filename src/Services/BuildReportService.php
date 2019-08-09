<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Http\Resources\ReportResultCollection;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Support\Maps\ReportResultMap;

class BuildReportService
{
    /** @var bool $paginate */
    public $paginate = true;

    /** @var Report $report */
    protected $report;

    /** @var Collection $parameters */
    protected $parameters;

    /** @var Collection $headings */
    protected $headings;

    /** @var Builder $query */
    protected $query;

    /**
     * Create a new Render Report Service Instance
     *
     * @param Report $report
     * @param array $parameters
     */
    public function __construct(Report $report, array $parameters = [])
    {
        $this->report = $report;
        $this->parameters = collect($parameters);

        $this->buildReportQuery();
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
     * Get Report Headings
     *
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

        if ($this->report->joins->count()) {
            $this->addJoins();
        }

        if ($this->report->selects->count()) {
            $this->addSelects();
        }

        if ($this->report->where) {
            $this->addWhere();
        }

        if ($this->report->groupby) {
            $this->addGroupBy();
        }

        if ($this->report->having) {
            $this->addHaving();
        }

        if ($this->report->orderby) {
            $this->addOrderBy();
        }

        return $this->query;
    }

    /**
     * Add Report Selects
     *
     * @return void
     */
    protected function addSelects(): void
    {
        $this->report->selects->each(function (ReportSelect $select) {
            $this->query->selectRaw("{$select->column} AS '{$select->alias}'");
        });
    }

    /**
     * Add Report Joins
     *
     * @return void
     */
    protected function addJoins(): void
    {
        $this->report->joins->each(function (ReportJoin $join) {
            $this->buildJoin($join);
        });
    }

    /**
     * Add Where to Query
     *
     * @return void
     */
    protected function addWhere(): void
    {
        $this->parameters->each(function ($value, $field) {
            $this->report->where = preg_replace("/(\{{$field}\}|\{\{{$field}\}\})/i", "{$value}", $this->report->where);
        });

        $this->query->whereRaw($this->report->where);
    }

    /**
     * Add Group By
     *
     * @return void
     */
    public function addGroupBy(): void
    {
        $this->query->groupBy(DB::raw($this->report->groupby));
    }

    /**
     * Add Having
     *
     * @return void
     */
    public function addHaving(): void
    {
        $this->query->havingRaw($this->report->having);
    }

    /**
     * Add Order By
     *
     * @return void
     */
    protected function addOrderBy(): void
    {
        $this->query->orderByRaw($this->report->orderby);
    }

    /**
     * Build the Join
     *
     * @param ReportJoin $join
     * @return void
     */
    private function buildJoin(ReportJoin $join): void
    {
        switch ($join->type) {
            case 'left':
                $this->query->leftJoin($join->table, $join->first, $join->operator, $join->second);
                break;
            case 'right':
                $this->query->rightJoin($join->table, $join->first, $join->operator, $join->second);
                break;
            default:
                $this->query->join($join->table, $join->first, $join->operator, $join->second);
        }
    }

    /**
     * Get Report Data
     *
     * @return mixed
     */
    private function data()
    {
        if ($this->report->shouldShowData()) {
            if ($this->paginate) {
                $results = $this->query->paginate($this->report->display_limit);

                $results->getCollection()->transform(function ($attributes) {
                    $map = new ReportResultMap($attributes);

                    return $map->format($this->report->selects);
                });

                return $results;
            }

            return $this->query->get();
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