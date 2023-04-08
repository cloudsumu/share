<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePromocodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create promocodes', Promocodes::class);
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
