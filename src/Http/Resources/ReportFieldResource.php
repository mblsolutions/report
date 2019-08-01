<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\Models\ReportFieldType;

class ReportFieldResource extends JsonResource
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
            'label' => $this->label,
            'type' => $this->type ?? ReportFieldType::TEXT,
            'model' => $this->model,
            'alias' => $this->alias,
            'model_select_value' => $this->model_select_value,
            'model_select_name' => $this->model_select_name,
        ];
    }

}
