<?php

namespace App\Http\Requests\Sakip;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Generate Report Request
 *
 * Validates report generation parameters including type, period, and indicators.
 */
class GenerateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Report::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'report_type' => 'required|in:monthly,quarterly,semester,annual,custom',
            'period' => 'required|string|max:20|date_format:Y-m',
            'category' => 'required|in:performance,assessment,compliance,summary',
            'indicators' => 'required|array|min:1',
            'indicators.*' => 'exists:performance_indicators,id',
            'format' => 'required|in:pdf,excel,word',
            'include_charts' => 'sometimes|boolean',
            'include_comparison' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'report_type.required' => 'Tipe laporan wajib dipilih.',
            'report_type.in' => 'Tipe laporan tidak valid.',
            'period.required' => 'Periode laporan wajib diisi.',
            'period.date_format' => 'Format periode harus YYYY-MM.',
            'category.required' => 'Kategori laporan wajib dipilih.',
            'indicators.required' => 'Minimal satu indikator harus dipilih.',
            'indicators.*.exists' => 'Indikator yang dipilih tidak valid.',
            'format.required' => 'Format laporan wajib dipilih.',
            'format.in' => 'Format laporan harus pdf, excel, atau word.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'report_type' => 'tipe laporan',
            'period' => 'periode',
            'category' => 'kategori',
            'indicators' => 'indikator',
            'format' => 'format',
        ];
    }
}
