<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTicketUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:open,diagnosing,waiting_parts,repaired,delivered,closed',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'issue' => 'sometimes|string',
            'completed_at' => 'nullable|date',
        ];
    }
}
