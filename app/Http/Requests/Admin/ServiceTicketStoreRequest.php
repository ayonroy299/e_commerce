<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTicketStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'serial_no' => 'nullable|string|max:255',
            'issue' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}
