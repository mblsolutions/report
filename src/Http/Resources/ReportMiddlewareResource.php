<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportMiddlewareResource extends JsonResource
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
            'middleware' => $this->middleware,
            'deleted_at' => $this->deleted_at
        ];
    }

}
