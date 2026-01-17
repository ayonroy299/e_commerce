<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_branch_id' => 'sometimes|exists:branches,id',
            'from_warehouse_id' => 'sometimes|exists:warehouses,id',
            'to_warehouse_id' => 'sometimes|exists:warehouses,id',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,pending,sent,received,cancelled',
            'lines' => 'sometimes|array|min:1',
            'lines.*.id' => 'sometimes|exists:stock_transfer_lines,id',
            'lines.*.product_id' => 'required_with:lines|exists:products,id',
            'lines.*.variation_id' => 'nullable|exists:product_variations,id',
            'lines.*.quantity' => 'required_with:lines|numeric|min:0.01',
        ];
    }
}
