<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\Models\ReportJoinType;

class ReportJoinResource extends JsonResource
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
            'type' => $this->type ?? ReportJoinType::INNER_JOIN,
            'table' => $this->table,
            'first' => $this->first,
            'operator' => $this->operator,
            'second' => $this->second,
            'deleted_at' => $this->deleted_at
        ];
    }

}
