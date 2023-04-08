<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update users', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|min:3|max:20',
            'last_name' => 'required|min:3|max:20',
            'email' => [
                'required',
                Rule::unique('users')->ignore($this->user->id, 'id'),
                'email',
            ],
            'mobile' => 'required|string|',
            'avatar' => 'sometimes|file|max:2048',
            'country_code' => 'sometimes',
        ];
    }
}
