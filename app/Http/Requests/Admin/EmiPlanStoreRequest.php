<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmiPlanStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:emi_plans,name',
            'tenor_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'interest_type' => 'required|in:flat,declining',
            'down_payment_percentage' => 'required|numeric|min:0|max:100',
            'late_fee_type' => 'required|in:fixed,percentage',
            'late_fee_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}
