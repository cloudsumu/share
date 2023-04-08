<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update notifications', Notification::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => 'sometimes|file|max:1024',
            'notify_type' => [
                'required',
                Rule::in(['all', 'user', 'provider'])
            ],
            'status' => [
                'required',
                Rule::in(['1', '0']),
            ],
            'description' => 'required',
            'expiry_date' => 'required|date',
        ];
    }
}
