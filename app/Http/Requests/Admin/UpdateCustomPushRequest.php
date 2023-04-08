<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomPushRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update customPushes', CustomPush::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'send_to' => [
                'required',
                Rule::in(['ALL', 'USERS', 'PROVIDERS']),
            ],
            // 'condition' => [
            //     'required',
            //     Rule::in(['ACTIVE', 'LOCATION', 'RIDES', 'AMOUNT']),
            // ],
            // 'condition_data' => 'required|string',
            'message' => 'required|string',
            // 'sent_to' => 'sometimes|integer',
            // 'schedule_at' => 'sometimes',
        ];
    }
}
