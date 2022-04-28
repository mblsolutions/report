<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use MBLSolutions\Report\Support\HandlesAuthenticatable;

class ReportIndexResource extends JsonResource
{
    use HandlesAuthenticatable;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'active' => $this->getAttribute('active'),
            'admin_only' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('admin_only')),
            'connection' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('connection')),
            'created_at' => $this->getAttribute('created_at'),
            'deleted_at' => $this->getAttribute('deleted_at'),
            'description' => $this->getAttribute('description'),
            'display_limit' => $this->getAttribute('display_limit'),
            'groupby' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('groupby')),
            'having' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('having')),
            'id' => $this->getAttribute('id'),
            'name' => $this->getAttribute('name'),
            'orderby' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('orderby')),
            'show_data' => $this->getAttribute('show_data'),
            'show_totals' => $this->getAttribute('show_totals'),
            'table' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('table')),
            'updated_at' => $this->getAttribute('updated_at'),
            'where' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('where')),
        ];
    }

}
