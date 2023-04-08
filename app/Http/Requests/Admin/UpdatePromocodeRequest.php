<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromocodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update promocodes', Promocode::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'promo_code' => 'required|string',
            'percentage' => 'required',
            'max_amount' => 'required',
            'promo_description' => 'required|string',
            'expiration' => 'required|',
            'status' => [
                'required',
                Rule::in(['1', '0']),
            ]
        ];
    }
}
