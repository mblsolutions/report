<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\Models\ReportSelectType;

class ReportSelectResource extends JsonResource
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
            'column' => $this->column,
            'alias' => $this->alias,
            'type' => $this->type ?? ReportSelectType::STRING,
            'column_order' => $this->column_order,
        ];
    }

}
