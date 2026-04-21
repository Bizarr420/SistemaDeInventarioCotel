<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'internal_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'internal_code')->ignore($productId),
            ],
            'part_number' => ['nullable', 'string', 'max:120'],
            'item' => ['nullable', 'string', 'max:120'],
            'name_item' => ['required', 'string', 'max:150'],
            'cnd' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:20'],
            'mac' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'end_of_support' => ['nullable', 'date'],
            'compatibility_status' => ['nullable', 'string', 'max:50'],
            'operational_capacity' => ['nullable', 'numeric', 'between:0,100'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_value' => ['nullable', 'numeric', 'min:0'],
            'current_accounting_value' => ['nullable', 'numeric', 'min:0'],
            'technical_value' => ['nullable', 'numeric', 'min:0'],
            'obsolescence_status' => ['nullable', 'in:active,obsolete,critical'],
            'obsolescence_criteria' => ['nullable', 'array'],
        ];
    }
}
