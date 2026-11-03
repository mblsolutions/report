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
use InvalidArgumentException;
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
use MBLSolutions\Report\Support\HandlesAuthenticatable;
use MBLSolutions\Report\Support\Maps\ReportResultMap;

class BuildReportService
{
    use HandlesAuthenticatable;

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
    public function __construct(Report $report, array $parameters = [], bool $paginate = true, mixed $authenticatable = null)
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
        $this->buildReportQuery([], true);

        $result = new Collection([
            'headings' => $this->headings(),
            'data' => $this->data(0, $limit),
            'totals' => false,
            'drivers' => $this->exportDrivers(),
            'parameters' => $this->getFormattedParameters($this->parameters),
            'result_limit' => $limit
        ]);

        if ($this->authenticatableIsAdmin()) {
            $result->put('raw', $this->getRawQuery());
        }

        Event::dispatch(new ReportRendered($this->report));

        return $result;
    }

    /**
     * Get Parameters
     *
     * @param Collection|array $parameters
     * @return Collection
     */
    public function getFormattedParameters($parameters): Collection
    {
        if (is_array($parameters)) {
            $parameters = new Collection($parameters);
        }

        return $parameters->map(fn($value, $alias) => $this->formatParameters($value, $alias));
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
                    $model = $namespace::where($field->getAttribute('model_select_value'), '=', $value)->first();

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
    public function buildReportQuery(array $except = [], $preview = false): Builder
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

        // Check if query contains aggregates, as without the GROUP BY in inner query, aggregates won't work
        $has_aggregate = false;
        if ($preview) {
            $has_aggregate = $this->report->selects->some(function (ReportSelect $select) {
                return preg_match('/\b(SUM|MAX|MIN|COUNT|AVG)\s*\([^\)]*\)/i', $select->column);
            });
        }

        if ((!$preview || $has_aggregate) && !in_array('group', $except, true) && !empty($this->report->groupby)) {
            $this->addGroupBy();
        }

        if (!in_array('having', $except, true) && !empty($this->report->having)) {
            $this->addHaving();
        }

        if (!in_array('order', $except, true) && !empty($this->report->orderby)) {
            $this->addOrderBy();
        }

        // Used only for Previews to wrap query with GROUP BY after setting a limit
        if ($preview && !$has_aggregate && !in_array('group', $except, true) && !empty($this->report->groupby)) {

            // Split groupby string by commas and trim spaces
            $groupby_fields = array_map('trim', explode(',', $this->report->groupby));

            $groupby_alias = [];
            $outer_query_selects = [];
            $select_aliases = [];

            // Find the Group By alias from Selects
            $this->report->selects->each(function (ReportSelect $select) use (&$groupby_fields, &$groupby_alias, &$select_aliases, &$outer_query_selects) {
                $outer_query_selects[] = $select->alias ? $select->alias : $select->column;
                foreach ($groupby_fields as $field) {
                    if ($select->column == $field) {
                        $groupby_alias[] = $select->alias;
                        $select_aliases[] = $select->column;
                    }
                }
            });

            // Check for missing groupby fields
            foreach ($groupby_fields as $field) {
                if (!in_array($field, $select_aliases)) {
                    // If the field is not in selects, we need to inject it
                    $field_alias = str_replace('.', '_', preg_replace('/[^a-zA-Z0-9.]+/', '', $field));
                    $this->query->addSelect(DB::raw($field . ' AS ' . $field_alias)); // Injecting into SELECT
                    $groupby_alias[] = $field_alias;
                }
            }

            // If we have a valid groupby alias
            if (!empty($groupby_alias)) {
                $this->query->limit(config('report.preview_results_limit', 15000));

                // Wrap the query inside another SELECT with GROUP BY
                $this->query = DB::table(DB::raw("({$this->query->toSql()}) AS query_data"))
                    ->mergeBindings($this->query);

                $this->query->groupBy(...$groupby_alias);

                $this->query->select($outer_query_selects); // Apply the select columns from the inner query to outer query
            } else {
                $this->addGroupBy();
            }
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
        // Work on a copy so the report model keeps its placeholders
        $where = $this->report->where;
        $values = [];

        $this->report->fields->each(function (ReportField $field) use (&$where, &$values) {
            $value = $this->parameters->get($field->alias);

            if ($value == null) {
                $where = $this->replaceParameter($field->alias, $value, $where);
            } elseif (!is_scalar($value)) {
                throw new InvalidArgumentException(sprintf('Report parameter [%s] must be a single value.', $field->alias));
            } else {
                $values[$field->alias] = $value;
            }
        });

        if ($this->checkWhereIsNotEmpty($where)) {
            [$sql, $bindings] = $this->bindParameters($this->cleanWhereSyntax($where), $values);

            $this->query->whereRaw($sql, $bindings);
        }
    }

    /**
     * Swap {alias} placeholders for ? and collect the values as bindings.
     * A quoted literal holding a placeholder becomes one ? bound to the filled-in text.
     *
     * @param string $where
     * @param array $values
     * @return array
     */
    protected function bindParameters(string $where, array $values): array
    {
        if ($values === []) {
            return [$where, []];
        }

        $lookup = [];

        foreach ($values as $alias => $value) {
            $lookup[strtolower($alias)] ??= $value;
        }

        $aliases = implode('|', array_map(static fn ($alias) => preg_quote($alias, '/'), array_keys($values)));
        $placeholder = '\{\{(' . $aliases . ')\}\}|\{(' . $aliases . ')\}';
        $valueFor = static fn (array $match) => $lookup[strtolower(($match[1] ?? '') !== '' ? $match[1] : $match[2])];

        $sql = '';
        $bindings = [];
        $length = strlen($where);
        $i = 0;

        while ($i < $length) {
            $char = $where[$i];

            if ($char === "'" || $char === '"' || $char === '`') {
                $end = $this->findClosingQuote($where, $i);
                $literal = substr($where, $i, $end - $i + 1);

                if ($char !== '`' && $end < $length) {
                    $count = 0;
                    $text = preg_replace_callback(
                        '/' . $placeholder . '/i',
                        static fn ($match) => (string) $valueFor($match),
                        $this->unescapeLiteral(substr($literal, 1, -1), $char),
                        -1,
                        $count
                    );

                    if ($count > 0) {
                        $sql .= '?';
                        $bindings[] = $text;
                        $i = $end + 1;
                        continue;
                    }
                }

                $sql .= $literal;
                $i = $end + 1;
                continue;
            }

            if ($char === '{' && preg_match('/\G(?:' . $placeholder . ')/i', $where, $match, 0, $i)) {
                $sql .= '?';
                $bindings[] = $this->bareValue($valueFor($match));
                $i += strlen($match[0]);
                continue;
            }

            $sql .= $char;
            $i++;
        }

        return [$sql, $bindings];
    }

    /**
     * Index of the closing quote, or the string length if it is never closed
     *
     * @param string $where
     * @param int $start
     * @return int
     */
    protected function findClosingQuote(string $where, int $start): int
    {
        $quote = $where[$start];
        $length = strlen($where);

        for ($i = $start + 1; $i < $length; $i++) {
            if ($where[$i] === '\\' && $quote !== '`') {
                $i++;
                continue;
            }

            if ($where[$i] === $quote) {
                if (($where[$i + 1] ?? '') === $quote) {
                    $i++;
                    continue;
                }

                return $i;
            }
        }

        return $length;
    }

    /**
     * Undo SQL escapes so the bound text matches what the literal meant
     *
     * @param string $text
     * @param string $quote
     * @return string
     */
    protected function unescapeLiteral(string $text, string $quote): string
    {
        return preg_replace_callback(
            '/\\\\([\\\\\'"])|' . preg_quote($quote . $quote, '/') . '/',
            static fn ($match) => $match[1] ?? $quote,
            $text
        );
    }

    /**
     * Bind whole numbers as int, as they acted when pasted into the SQL
     *
     * @param mixed $value
     * @return mixed
     */
    protected function bareValue($value)
    {
        if (is_string($value) && preg_match('/^(-?)0*(\d+)$/', $value, $match)
            && (string) (int) $value === ($match[2] === '0' ? '0' : $match[1] . $match[2])) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * Check if where statement is not empty
     *
     * @param string|null $where
     * @return bool
     */
    protected function checkWhereIsNotEmpty(?string $where = null): bool
    {
        return !empty(preg_replace('/\s+/', '', $where ?? $this->report->where));
    }

    /**
     * Clean Where syntax, removing keywords post replacement
     * .e.g WHERE AND users.id = 1
     *
     * @param string|null $where
     * @return string
     */
    protected function cleanWhereSyntax(?string $where = null): string
    {
        return trim(preg_replace('/\A(\s*)(AND|OR|IN)(?=\s)/i', '', $where ?? $this->report->where, 1));
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
            preg_match_all("/([a-z._]+)(\s?)(>=|<=|=|<|>|!=|<>)(\s?)(\\'?)(\{{$field}\})(\s?)([0-9:]*)(\\'?)/", $subject, $matches);

            foreach ($matches[0] as $match) {
                $match = trim($match);

                // Check for a leading "AND" or "OR"
                if (preg_match("/\s*(AND|OR)\s+" . preg_quote($match, '/') . "/i", $subject)) {
                    $subject = preg_replace("/\s*(AND|OR)\s+" . preg_quote($match, '/') . "/i", '', $subject);
                }
                // Check for a trailing "AND" or "OR"
                else if (preg_match("/" . preg_quote($match, '/') . "\s+(AND|OR)\s*/i", $subject)) {
                    $subject = preg_replace("/" . preg_quote($match, '/') . "\s+(AND|OR)\s*/i", '', $subject);
                }
                // If neither, simply remove the match (no AND/OR condition)
                else {
                    $subject = str_replace($match, '', $subject);
                }
            }

            return $subject;
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
    private function data(int $offset = 0, ?int $limit = null)
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
