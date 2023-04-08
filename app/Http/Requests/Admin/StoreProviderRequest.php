<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create storeProviders', StoreProvider::class);
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
            'email' => 'required|email|max:320',
            'mobile' => 'required|string|',
            'agent_id' => 'exclude_if:agent_id,NULL',
            'password' => 'required|min:8',
            'avatar' => 'sometimes|file|max:2048',
            'country_code' => 'sometimes',
        ];
    }
}
