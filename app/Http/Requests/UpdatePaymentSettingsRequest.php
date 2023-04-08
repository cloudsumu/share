<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
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
        return [
            'payment_daily_target' => 'required|integer|',
            'tax_percentage' => 'required|integer|max:100',
            'commission_percentage' => 'required|integer|max:100',
            // 'provider_commission_percentage' => 'required|integer|max:100',
            'peak_percentage' => 'required|integer|max:100',
            'minimum_negative_balance' => 'required|max:0|integer|',
            'cancel_charge' => 'required|integer',
            
        ];
    }
}
