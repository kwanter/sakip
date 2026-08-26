<?php

namespace App\Http\Requests\Sakip;

use App\Constants\ValidationRules;
use App\Constants\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Data Collection Form Request
 *
 * Handles validation for Performance Data create and update operations.
 * Eliminates duplicate validation logic from DataCollectionController.
 *
 * @package App\Http\Requests\Sakip
 */
class DataCollectionFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Policy-based authorization will be handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $performanceDataId = $this->route('performanceData')?->id;
        $indicatorId = $this->input('indicator_id');

        return [
            'indicator_id' => [
                'required',
                'string',
                'exists:performance_indicators,id',
            ],
            'period' => [
                'required',
                'date',
                'after_or_equal:' . date('Y-01-01'),
                'before_or_equal:' . date('Y-12-31'),
            ],
            'actual_value' => [
                'required',
                'numeric',
            ],
            'target_value' => [
                'nullable',
                'numeric',
            ],
            'performance_percentage' => [
                'nullable',
                'numeric',
                'min:' . ValidationRules::MIN_PERCENTAGE,
                'max:' . config('sakip.performance.max_percentage', 200),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:' . ValidationRules::LONG_TEXT_MAX_LENGTH,
            ],
            'status' => [
                'nullable',
                'string',
                'in:' . Status::DRAFT . ',' . Status::SUBMITTED . ',' . Status::VALIDATED . ',' . Status::APPROVED . ',' . Status::REJECTED,
            ],
            'evidence_files' => [
                'nullable',
                'array',
                'max:10',
            ],
            'evidence_files.*' => [
                'nullable',
                'file',
                'max:' . ValidationRules::MAX_FILE_SIZE,
                'mimes:' . implode(',', ValidationRules::allowedDocumentExtensions()),
            ],
            'existing_files' => [
                'nullable',
                'array',
            ],
            'existing_files.*' => [
                'string',
                'exists:evidence_documents,id',
            ],
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
            'indicator_id.required' => 'Indikator kinerja wajib dipilih.',
            'indicator_id.exists' => 'Indikator kinerja yang dipilih tidak valid.',
            'period.required' => 'Periode wajib diisi.',
            'period.date' => 'Periode harus berupa tanggal yang valid.',
            'period.after_or_equal' => 'Periode harus dalam tahun berjalan.',
            'period.before_or_equal' => 'Periode harus dalam tahun berjalan.',
            'actual_value.required' => 'Nilai aktual wajib diisi.',
            'actual_value.numeric' => 'Nilai aktual harus berupa angka.',
            'target_value.numeric' => 'Nilai target harus berupa angka.',
            'performance_percentage.numeric' => 'Persentase kinerja harus berupa angka.',
            'performance_percentage.min' => 'Persentase kinerja minimal :min%.',
            'performance_percentage.max' => 'Persentase kinerja maksimal :max%.',
            'notes.max' => 'Catatan maksimal :max karakter.',
            'status.in' => 'Status harus salah satu dari: draft, submitted, validated, approved, atau rejected.',
            'evidence_files.array' => 'File bukti harus berupa array.',
            'evidence_files.max' => 'Maksimal :max file bukti yang dapat diunggah.',
            'evidence_files.*.file' => 'File bukti harus berupa file yang valid.',
            'evidence_files.*.max' => 'Ukuran file bukti maksimal :max KB.',
            'evidence_files.*.mimes' => 'Tipe file bukti harus salah satu dari: pdf, doc, docx, xls, xlsx, jpg, jpeg, png.',
            'existing_files.*.exists' => 'File yang dipilih tidak ditemukan.',
        ];
    }

    /**
     * Get custom attributes for validator error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'indicator_id' => 'Indikator Kinerja',
            'period' => 'Periode',
            'actual_value' => 'Nilai Aktual',
            'target_value' => 'Nilai Target',
            'performance_percentage' => 'Persentase Kinerja',
            'notes' => 'Catatan',
            'status' => 'Status',
            'evidence_files' => 'File Bukti',
            'existing_files' => 'File yang Ada',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before validation occurs. Use it to
     * sanitize or normalize input data.
     */
    protected function prepareForValidation(): void
    {
        // Trim string fields and format date
        $period = $this->input('period');

        $this->merge([
            'notes' => $this->input('notes') ? trim($this->input('notes')) : null,
            'period' => $period ? date('Y-m-d', strtotime($period)) : null,
        ]);
    }

    /**
     * Get computed performance percentage if not provided.
     *
     * This helper method calculates performance percentage based on
     * actual and target values using the configured calculation method.
     *
     * @return float|null
     */
    public function getComputedPerformancePercentage(): ?float
    {
        $actual = (float) $this->input('actual_value', 0);
        $target = (float) $this->input('target_value', 0);

        // If target is zero or null, cannot calculate percentage
        if ($target == 0) {
            return $actual > 0 ? 100 : 0;
        }

        // Handle negative targets (cost reduction goals)
        if ($target < 0) {
            if ($actual < 0) {
                $percentage = abs($actual / $target) * 100;
                return min($percentage, config('sakip.performance.max_percentage', 200));
            }
            return 0;
        }

        // Handle negative actual with positive target
        if ($actual < 0) {
            return 0;
        }

        // Standard calculation
        $percentage = ($actual / $target) * 100;
        return round(max(0, $percentage), 2);
    }
}
