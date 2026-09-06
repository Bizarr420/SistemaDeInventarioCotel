<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'operation_type' => ['required', 'in:entry,exit,transfer,return,adjustment,disposal'],
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('type', 'service')),
            ],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['nullable', 'in:in,out'],
            'status' => ['nullable', 'in:pending,partially_liquidated,completed,cancelled,in_transit,received'],
            'reason' => ['required_if:operation_type,entry,exit,adjustment,disposal', 'nullable', 'string', 'max:120'],
            'origin' => ['required_if:operation_type,entry', 'nullable', 'string', 'max:120'],
            'adjustment_direction' => ['required_if:operation_type,adjustment', 'nullable', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
            'technician_id' => ['required_if:operation_type,exit', 'nullable', 'exists:users,id'],
        ];
    }
}
