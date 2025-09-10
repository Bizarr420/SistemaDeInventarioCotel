<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'internal_code' => 'nullable|string|max:100',         // COD.INT.
            'part_number'   => 'nullable|string|max:120',
            'item'          => 'nullable|string|max:120',
            'name_item'     => 'required|string|max:150',
            'cnd'           => 'nullable|string|max:80',
            'unit'          => 'nullable|string|max:20',
            'mac'           => 'nullable|string|max:50',
            'description'   => 'nullable|string',
            'note'          => 'nullable|string',
            'category_id'   => 'nullable|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
        ];
    }
}
