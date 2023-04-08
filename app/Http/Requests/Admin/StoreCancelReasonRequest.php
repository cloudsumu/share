<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCancelReasonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create cancelReasons', CancelReason::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reason' => 'required|min:3|max:60',
            'for' => [
                'required',
                Rule::in(['user', 'provider'])
            ],
            'status' => [
                'required',
                Rule::in(['1', '0'])
            ]
        ];
    }
}
