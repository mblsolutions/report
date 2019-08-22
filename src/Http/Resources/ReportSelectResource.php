<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\DataType\CastString;

class ReportSelectResource extends JsonResource
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
            'column' => $this->column,
            'alias' => $this->alias,
            'type' => $this->type ?? CastString::class,
            'column_order' => $this->column_order,
            'deleted_at' => $this->deleted_at
        ];
    }

}
