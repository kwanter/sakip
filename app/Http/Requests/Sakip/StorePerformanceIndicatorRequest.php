<?php

namespace App\Http\Requests\Sakip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceIndicatorRequest extends FormRequest
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
        return [
            "instansi_id" => "required|exists:instansis,id",
            "sasaran_strategis_id" => "nullable|exists:sasaran_strategis,id",
            "program_id" => "nullable|exists:programs,id",
            "kegiatan_id" => "nullable|exists:kegiatans,id",
            "code" =>
                "required|string|max:50|unique:performance_indicators,code",
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "measurement_unit" => "required|string|max:100",
            "measurement_type" => [
                "nullable",
                Rule::in(["percentage", "number", "ratio", "index"]),
            ],
            "data_source" => "required|string|max:255",
            "collection_method" => [
                "required",
                Rule::in([
                    "manual",
                    "automated",
                    "survey",
                    "interview",
                    "observation",
                    "document_review",
                ]),
            ],
            "calculation_formula" => "nullable|string",
            "frequency" => [
                "required",
                Rule::in(["monthly", "quarterly", "semester", "annual"]),
            ],
            "category" => [
                "required",
                Rule::in(["input", "output", "outcome", "impact"]),
            ],
            "weight" => "nullable|numeric|min:0|max:100",
            "is_mandatory" => "boolean",
            "metadata" => "nullable|array",
            // Target validation
            "targets" => "required|array|min:1",
            "targets.*.year" => "required|integer|min:" . date("Y"),
            "targets.*.target_value" => "required|numeric|min:0",
            "targets.*.minimum_value" => "nullable|numeric|min:0",
            "targets.*.justification" => "nullable|string|max:500",
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
            "instansi_id.exists" => "The selected instansi is invalid.",
            "code.required" => "The indicator code is required.",
            "code.unique" => "The indicator code has already been taken.",
            "name.required" => "The indicator name is required.",
            "measurement_unit.required" => "The measurement unit is required.",
            "data_source.required" => "The data source is required.",
            "collection_method.required" =>
                "The collection method is required.",
            "collection_method.in" =>
                "The collection method must be one of: manual, automated, survey, interview, observation, document_review.",
            "frequency.required" => "The frequency is required.",
            "frequency.in" =>
                "The frequency must be one of: monthly, quarterly, semester, annual.",
            "category.required" => "The category is required.",
            "category.in" =>
                "The category must be one of: input, output, outcome, impact.",
            "weight.required" => "The weight is required.",
            "weight.numeric" => "The weight must be a number.",
            "weight.min" => "The weight must be at least 0.",
            "weight.max" => "The weight may not be greater than 100.",
            // Target messages
            "targets.required" =>
                "Mohon tambahkan minimal satu target tahunan.",
            "targets.array" => "Target harus berupa array.",
            "targets.min" => "Mohon tambahkan minimal satu target tahunan.",
            "targets.*.year.required" => "Tahun target wajib diisi.",
            "targets.*.year.integer" =>
                "Tahun harus berupa angka tahun yang valid.",
            "targets.*.year.min" =>
                "Tahun harus lebih besar atau sama dengan tahun saat ini.",
            "targets.*.target_value.required" => "Nilai target wajib diisi.",
            "targets.*.target_value.numeric" =>
                "Nilai target harus berupa angka.",
            "targets.*.target_value.min" => "Nilai target harus minimal 0.",
            "targets.*.minimum_value.numeric" =>
                "Nilai minimum harus berupa angka.",
            "targets.*.minimum_value.min" => "Nilai minimum harus minimal 0.",
            "targets.*.justification.max" =>
                "Justifikasi tidak boleh lebih dari 500 karakter.",
        ];
    }
}
