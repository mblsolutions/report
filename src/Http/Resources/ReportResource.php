<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportSelect;

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
        return $this->fields;
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

}
