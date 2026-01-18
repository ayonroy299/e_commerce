<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts')->ignore($this->route('account')),
            ],
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'is_active' => 'boolean',
        ];
    }
}
