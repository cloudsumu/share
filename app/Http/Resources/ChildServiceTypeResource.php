<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChildServiceTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        dd($this->children_recursive);
        return [
            'id'                 => $this->id,
            'parent_id'          => $this->parent_id,
            'name'               => $this->name,
            'image'              => $this->image,
            'marker'             => $this->marker,
            'fixed'              => $this->fixed,
            'price'              => $this->price,
            'type_price'         => $this->type_price,
            'calculator'         => $this->calculator,
            'description'        => $this->description,
            'status'             => $this->status,
            //'children_recursive' => ServiceTypeResource::collection($this->children_recursive ?? null),
        ];
    }
}
