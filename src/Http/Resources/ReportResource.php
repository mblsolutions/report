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
            'show_data' => $this->show_data ?? true,
            'show_totals' =>  $this->show_totals ?? false,
            'active' =>  $this->active ?? true,
            'fields' => new ReportFieldCollection($this->getReportFields()),
            'selects' => new ReportSelectCollection($this->getReportSelects()),
            'joins' => new ReportJoinCollection($this->getReportJoins())
        ];
    }

    /**
     * Get the Report Fields
     *
     * @return mixed
     */
    public function getReportFields(): Collection
    {
        $fields = $this->fields;

        if ($fields->count() === 0) {
            $fields = collect([
                new ReportField()
            ]);
        }

        return $fields;
    }


    /**
     * Get the Report Selects
     *
     * @return mixed
     */
    public function getReportSelects()
    {
        $selects = $this->selects;

        if ($selects->count() === 0) {
            $selects = collect([
                new ReportSelect()
            ]);
        }

        return $selects;
    }

    /**
     * Get the Report Joins
     *
     * @return mixed
     */
    public function getReportJoins()
    {
        $joins = $this->joins;

        if ($joins->count() === 0) {
            $joins = collect([
                new ReportJoin()
            ]);
        }

        return $joins;
    }

}
