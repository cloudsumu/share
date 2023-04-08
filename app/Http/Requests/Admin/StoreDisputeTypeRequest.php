<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisputeTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create disputeTypes', DisputeType::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dispute_name' => 'required|min:3|max:50',
            'dispute_type' => [
                'required',
                Rule::in(['user', 'agent', 'provider'])
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive'])
            ]
        ];
    }
}
