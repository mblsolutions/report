<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\Interfaces\PopulatesReportOption;
use MBLSolutions\Report\Models\ReportFieldType;

class ReportFieldResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @codeCoverageIgnore
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type ?? ReportFieldType::TEXT,
            'model' => $this->model,
            'alias' => $this->alias,
            'model_select_value' => $this->model_select_value,
            'model_select_name' => $this->model_select_name,
            'deleted_at' => $this->deleted_at,
            'options' => $this->when($this->model, function () {
                return $this->getOptions(new $this->model);
            })
        ];
    }

    /**
     * Get Options
     *
     * @param PopulatesReportOption $model
     * @return array
     * @codeCoverageIgnore
     */
    public function getOptions(PopulatesReportOption $model): array
    {
        return $model::options($this->model_select_value, $this->model_select_name)->sortBy($this->model_select_name)->toArray();
    }

}
