<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceTypeGeoFenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fixed' => 'required|',
            'price' => 'required|',
            'minute' => 'required|',
            'hour' => 'nullable',
            'distance' => 'required|',
            'non_geo_price' => 'sometimes|',
            'city_limits' => 'sometimes|',
            'geo_fencing_id' => 'required|exists:geo_fencings,id',
        ];
    }
}
