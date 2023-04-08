<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create serviceTypes', ServiceType::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'fixed' => 'required|numeric',
            // 'price' => 'sometimes|numeric',
            // 'type_price' => 'sometimes|numeric',
            'calculator' => [
                'sometimes',
                Rule::in(['MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR'])
            ],
            'status' => [
                'required',
                Rule::in(['1', '0'])
            ],
            // 'parent_id' => 'exclude_if:parent_id,""|exists:service_types,id',
            'image' => 'sometimes|file|max:1024',
            'marker' => 'sometimes|file|max:1024',
            'name' => 'required',
            'description' => 'required',
            'night_fare' => 'required',
            'waiting_free_mins' => 'required',
            'waiting_min_charge' => 'required',
            'roundtrip_km' => 'required',
            'outstation_km' => 'required',
            'outstation_driver' => 'required',
            'capacity' => 'required',
        ];

        return $rules;
    }
}
