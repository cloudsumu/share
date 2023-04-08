<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update banners', Banner::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url' => 'nullable|file|max:2048',
            'video' => 'nullable|string',
            'position' => 'nullable|integer',
            'status' => [
                'required',
                Rule::in(['1', '0'])
            ]
        ];
    }
}
