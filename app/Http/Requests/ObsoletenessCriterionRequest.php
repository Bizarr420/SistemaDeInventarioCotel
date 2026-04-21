<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ObsoletenessCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['tecnico', 'contable', 'auditor']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:end_of_support_days,operational_capacity_min,useful_life_below'],
            'parameters' => ['required', 'array'],
            'parameters.days' => ['nullable', 'integer', 'min:0'],
            'parameters.min' => ['nullable', 'integer', 'min:0', 'max:100'],
            'parameters.limit' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}