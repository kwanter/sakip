<?php

namespace App\Http\Requests\Sakip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerformanceIndicatorRequest extends FormRequest
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
        $indicatorId = $this->route('indicator');

        return [
            'instansi_id' => 'required|exists:instansis,id',
            'code' => 'required|string|max:50|unique:performance_indicators,code,' . $indicatorId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'measurement_unit' => 'required|string|max:100',
            'data_source' => 'required|string|max:255',
            'collection_method' => 'required|string|max:100',
            'calculation_formula' => 'nullable|string',
            'frequency' => ['required', Rule::in(['monthly', 'quarterly', 'semester', 'annual'])],
            'category' => ['required', Rule::in([
                'financial', 'service', 'internal', 'learning', 
                'stakeholder', 'compliance', 'strategic'
            ])],
            'weight' => 'required|numeric|min:0|max:100',
            'is_mandatory' => 'boolean',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'instansi_id.required' => 'The instansi field is required.',
            'instansi_id.exists' => 'The selected instansi is invalid.',
            'code.required' => 'The indicator code is required.',
            'code.unique' => 'The indicator code has already been taken.',
            'name.required' => 'The indicator name is required.',
            'measurement_unit.required' => 'The measurement unit is required.',
            'data_source.required' => 'The data source is required.',
            'collection_method.required' => 'The collection method is required.',
            'frequency.required' => 'The frequency is required.',
            'frequency.in' => 'The frequency must be one of: monthly, quarterly, semester, annual.',
            'category.required' => 'The category is required.',
            'category.in' => 'The category must be one of the predefined categories.',
            'weight.required' => 'The weight is required.',
            'weight.numeric' => 'The weight must be a number.',
            'weight.min' => 'The weight must be at least 0.',
            'weight.max' => 'The weight may not be greater than 100.',
        ];
    }
}