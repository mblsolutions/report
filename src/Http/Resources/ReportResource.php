<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportMiddleware;

class ReportResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'connection' => $this->connection ?? config('database.default'),
            'description' => $this->description,
            'display_limit' => $this->display_limit ?? 25,
            'show_data' => $this->show_data ?? true,
            'show_totals' =>  $this->show_totals ?? false,
            'table' => $this->table,
            'where' => $this->where,
            'groupby' => $this->groupby,
            'having' => $this->having,
            'orderby' => $this->orderby,
            'active' =>  $this->active ?? true,
            'fields' => new ReportFieldCollection($this->getReportFields()),
            'selects' => new ReportSelectCollection($this->getReportSelects()),
            'joins' => new ReportJoinCollection($this->getReportJoins()),
            'middleware' => new ReportMiddlewareCollection($this->getReportMiddleware()),
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Get the Report Fields
     *
     * @return mixed
     */
    protected function getReportFields(): Collection
    {
        return $this->fields->reject(function (ReportField $field) {

            // TODO figure this out

            /*$middleware = $this->middleware->filter(function (ReportMiddleware $reportMiddleware) use ($field) {
                return (new $reportMiddleware->middleware)->field($field) === false;
            });*/

            //return $middleware->count() > 0;
        });
    }


    /**
     * Get the Report Selects
     *
     * @return mixed
     */
    protected function getReportSelects()
    {
        return $this->selects;
    }

    /**
     * Get the Report Joins
     *
     * @return mixed
     */
    protected function getReportJoins()
    {
        return $this->joins;
    }

    /**
     * Get the Report Middleware
     *
     * @return mixed
     */
    protected function getReportMiddleware()
    {
        return $this->middleware;
    }

}
