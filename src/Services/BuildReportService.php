<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Driver\Export\ReportExport;
use MBLSolutions\Report\Driver\QueuedExport\QueuedReportExport;
use MBLSolutions\Report\Events\ReportRendered;
use MBLSolutions\Report\Middleware\ReportMiddleware;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Support\Builder\ChunkIncrementalResults;
use MBLSolutions\Report\Support\Maps\ReportResultMap;

class BuildReportService
{
    public bool $paginate = true;

    public Report $report;

    public Collection $parameters;

    protected ?Collection $headings = null;

    protected Collection $fields;

    protected Builder $query;

    /** @var mixed|null */
    protected $authenticatable;

    /**
     * Create a new Render Report Service Instance
     *
     * @param Report $report
     * @param array $parameters
     * @param bool $paginate
     * @param mixed|null $authenticatable
     */
    public function __construct(Report $report, array $parameters = [], bool $paginate = true, $authenticatable = null)
    {
        $this->paginate = $paginate;
        $this->report = $report;
        $this->parameters = new Collection($parameters);
        $this->authenticatable = $authenticatable;

        $this->fields = $this->report->fields;

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

        $result = new Collection([
            'headings' => $this->headings(),
            'data' => $this->data(),
            'totals' => false,
            'drivers' => $this->exportDrivers(),
            'raw' => $this->getRawQuery()
        ]);

        Event::dispatch(new ReportRendered($this->report));

        return $result;
    }

    /**
     * Render the Report
     *
     * @param int $limit
     * @return Collection
     */
    public function renderPreview(int $limit): Collection
    {
        $this->buildReportQuery();

        $result = new Collection([
            'headings' => $this->headings(),
            'data' => $this->data(0, $limit),
            'totals' => false,
            'drivers' => $this->exportDrivers(),
            'raw' => $this->getRawQuery(),
            'parameters' => $this->getFormattedParameters($this->parameters),
            'result_limit' => $limit
        ]);

        Event::dispatch(new ReportRendered($this->report));

        return $result;
    }

    /**
     * Get Parameters
     *
     * @param Collection|array $parameters
     * @return Collection
     */
    public  function getFormattedParameters($parameters): Collection
    {
        if (is_array($parameters)) {
            $parameters = new Collection($parameters);
        }

        return $parameters->map(fn ($value, $alias) => $this->formatParameters($value, $alias));
    }

    /**
     * Format Parameters
     *
     * @param mixed|null $value
     * @param $alias
     * @return array
     */
    protected function formatParameters($value, $alias): ?array
    {
        if ($alias === 'export_driver') {
            /** @var QueuedReportExport|ReportExport $driver */
            $driver = new $value;

            return [
                'name' => 'Export Driver',
                'value' => $driver->getName()
            ];
        }

        $field = $this->fields->firstWhere('alias', $alias);

        $fieldValue = $field ? $this->formatParameterValue($value, $field) : $value;

        return [
            'name' => $field ? $field->getAttribute('label') : $alias,
            'value' => $fieldValue ?? '-'
        ];
    }

    /**
     * Format Parameter Value
     *
     * @param null $value
     * @param ReportField $field
     * @return mixed
     */
    protected function formatParameterValue($value, ReportField $field)
    {
        if ($value === null) {
            return null;
        }

        switch ($field->getAttribute('type')) {
            case ReportFieldType::SELECT:
                $namespace = $field->getAttribute('model');

                if (new $namespace instanceof Model) {
                    $model = $namespace::where($field->getAttribute('model_select_value'),  '=', $value)->first();

                    return $model ? $model->getAttribute($field->getAttribute('model_select_name')) : $value;
                }

                return $value;
            case ReportFieldType::DATE:
                return Carbon::parse($value)->toDateString();
            case ReportFieldType::TIME:
                return Carbon::parse($value)->toTimeString();
            case ReportFieldType::DATETIME:
                return Carbon::parse($value)->toDateTimeString();
        }

        return $value;
    }

    /**
     * Get Rendered Report Chunk
     *
     * @param int $offset
     * @param int $limit
     * @param bool $toSql
     * @return Collection|string
     */
    public function getRenderedChunk(int $offset, int $limit, bool $toSql = false)
    {
        $builder = $this->buildReportQuery()->offset($offset)->limit($limit);

        if ($toSql) {
            return $builder->toSql();
        }

        return $builder->get();
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
     * @param array $except
     * @return Builder
     */
    public function buildReportQuery(array $except = []): Builder
    {

        if (!in_array('join', $except, true) && $this->report->joins->count()) {
            $this->addJoins();
        }

        if (!in_array('select', $except, true) && $this->report->selects->count()) {
            $this->addSelects();
        }

        if (!in_array('where', $except, true) && !empty($this->report->where)) {
            $this->addWhere();
        }

        if (!in_array('middleware', $except, true) && $this->report->middleware) {
            $this->handleMiddleware();
        }

        if (!in_array('group', $except, true) && !empty($this->report->groupby)) {
            $this->addGroupBy();
        }

        if (!in_array('having', $except, true) && !empty($this->report->having)) {
            $this->addHaving();
        }

        if (!in_array('order', $except, true) && !empty($this->report->orderby)) {
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
        return preg_replace('/\A(\s*)(AND)|\A(\s*)(OR)/i', '', $this->report->where, 1);
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
        if ($value == null) {
            return preg_replace("/((AND\s*)?([a-z._]+)(\s?)(>=|<=|=|<|>|!=|<>)(\s?)(\\'?)(\{{$field}\})(\s?)([0-9:]*)(\\'?)?)/", '', $subject);
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
            $middleware = new $reportMiddleware->middleware($this->getAuthenticatable());

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
    private function data(int $offset = 0, int $limit = null)
    {
        if ($limit) {
            $this->query = $this->query->offset($offset)->limit($limit);
        }

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
     * Get the Authenticatable Model
     *
     * @return Authenticatable|Model|null
     */
    public function getAuthenticatable()
    {
        if ($this->authenticatable && $authModel = config('report.authenticatable_model')) {
            return $authModel::find($this->authenticatable);
        }

        return Auth::user();
    }

}