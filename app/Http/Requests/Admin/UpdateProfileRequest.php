<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $available = array_keys(get_all_language());
        return [
            'name' => 'required|max:255|string',
            'avatar' => 'sometimes|file|max:2048',
            'mobile' => 'nullable|max:255|string',
            'language' => [
                'required',
                Rule::in($available)
            ],
        ];
    }
}
