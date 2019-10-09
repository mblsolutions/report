<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Events\ReportRendered;
use MBLSolutions\Report\Interfaces\ReportMiddleware;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ReportField;
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

        $this->query = DB::connection($this->report->connection)->table($this->report->table);
    }

    /**
     * Render the Report
     *
     * @return Collection
     */
    public function render(): Collection
    {
        $this->buildReportQuery();

        $result = collect([
            'headings' => $this->headings(),
            'data' => $this->data(),
            'totals' => false,
            'drivers' => $this->exportDrivers(),
            'raw' => $this->getRawQuery()
        ]);

        event(new ReportRendered($this->report));

        return $result;
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
     * Get available Export Drivers
     *
     * @return Collection
     */
    protected function exportDrivers(): Collection
    {
        $drivers = new ReportExportDrivers();

        return $drivers->all();
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
     * Get Report Selects
     *
     * @return Collection
     */
    public function selects(): Collection
    {
        return $this->report->selects;
    }

    /**
     * Get Report Query
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Build Report Query
     *
     * @return Builder
     */
    public function buildReportQuery(): Builder
    {
        if ($this->report->joins->count()) {
            $this->addJoins();
        }

        if ($this->report->selects->count()) {
            $this->addSelects();
        }

        if (!empty($this->report->where)) {
            $this->addWhere();
        }

        if ($this->report->middleware) {
            $this->handleMiddleware();
        }

        if (!empty($this->report->groupby)) {
            $this->addGroupBy();
        }

        if (!empty($this->report->having)) {
            $this->addHaving();
        }

        if (!empty($this->report->orderby)) {
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
        $this->report->fields->each(function (ReportField $field) {
            $this->report->where = $this->replaceParameter($field->alias, $this->parameters->get($field->alias), $this->report->where);
        });

        if ($this->checkWhereIsNotEmpty()) {
            $this->query->whereRaw($this->cleanWhereSyntax());
        }
    }

    /**
     * Check if where statement is not empty
     *
     * @return bool
     */
    protected function checkWhereIsNotEmpty(): bool
    {
        return !empty(preg_replace('/\s+/', '', $this->report->where));
    }

    /**
     * Clean Where syntax, removing keywords post replacement
     * .e.g WHERE AND users.id = 1
     *
     * @return string
     */
    protected function cleanWhereSyntax(): string
    {
        return preg_replace('/\A(\s*)(AND)|(\s*)(OR)/i', '', $this->report->where, 1);
    }

    /**
     * Replace Field Parameters
     *
     * @param string $field
     * @param $value
     * @param $subject
     * @return string|string[]|null
     */
    public function replaceParameter(string $field, $value, $subject)
    {
        //dump('performing replacement!');
        //dump($field);
        //dump($value);
        //dump($subject);

        if ($value == null) {
            return preg_replace("/((AND\s*)?([a-z._]+)\s(>=|<=|=|<|>|!=|<>)\s(\\'?)(\{{$field}\})(\\'?)?)/", null, $subject);
        }

        return preg_replace("/(\{{$field}\}|\{\{{$field}\}\})/i", $value, $subject);
    }

    /**
     * Handle Report Middleware
     *
     * @return void
     */
    protected function handleMiddleware(): void
    {
        $this->report->middleware->each(function ($reportMiddleware) {
            /** @var ReportMiddleware $middleware */
            $middleware = new $reportMiddleware->middleware;

            $this->query = $middleware->handle($this->query);
        });
    }

    /**
     * Add Group By
     *
     * @return void
     */
    protected function addGroupBy(): void
    {
        $this->query->groupBy(DB::raw($this->report->groupby));
    }

    /**
     * Add Having
     *
     * @return void
     */
    protected function addHaving(): void
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
            case 'left_join':
                $this->query->leftJoin($join->table, $join->first, $join->operator, $join->second);
                break;
            case 'right_join':
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

}