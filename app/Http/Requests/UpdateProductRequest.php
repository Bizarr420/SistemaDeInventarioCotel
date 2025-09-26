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
        ];
    }
}
