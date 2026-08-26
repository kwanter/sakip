<?php

namespace App\Http\Requests\Sakip;

use App\Constants\ValidationRules;
use App\Constants\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Program Form Request
 *
 * Handles validation for Program create and update operations.
 * Eliminates duplicate validation logic from controllers.
 *
 * @package App\Http\Requests\Sakip
 */
class ProgramFormRequest extends FormRequest
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
        $programId = $this->route('program')?->id;
        $instansiId = $this->input('instansi_id');

        return [
            'instansi_id' => [
                'required',
                'string',
                'exists:instansis,id',
            ],
            'sasaran_strategis_id' => [
                'nullable',
                'string',
                'exists:sasaran_strategis,id',
            ],
            'kode_program' => [
                'required',
                'string',
                'max:' . ValidationRules::CODE_MAX_LENGTH,
                Rule::unique('programs', 'kode_program')
                    ->where('instansi_id', $instansiId)
                    ->ignore($programId),
            ],
            'nama_program' => [
                'required',
                'string',
                'max:' . ValidationRules::NAME_MAX_LENGTH,
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:' . ValidationRules::LONG_TEXT_MAX_LENGTH,
            ],
            'status' => [
                'required',
                'string',
                'in:' . Status::DRAFT . ',' . Status::ACTIVE . ',' . Status::COMPLETED,
            ],
            'tahun_mulai' => [
                'required',
                'integer',
                'min:' . ValidationRules::MIN_YEAR,
                'max:' . ValidationRules::MAX_YEAR,
            ],
            'tahun_selesai' => [
                'required',
                'integer',
                'min:' . ValidationRules::MIN_YEAR,
                'max:' . ValidationRules::MAX_YEAR,
                'gte:tahun_mulai',
            ],
            'pagu_anggaran' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'output' => [
                'nullable',
                'string',
                'max:' . ValidationRules::MEDIUM_TEXT_MAX_LENGTH,
            ],
            'sasaran' => [
                'nullable',
                'string',
                'max:' . ValidationRules::MEDIUM_TEXT_MAX_LENGTH,
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
            'instansi_id.required' => 'Instansi wajib dipilih.',
            'instansi_id.exists' => 'Instansi yang dipilih tidak valid.',
            'sasaran_strategis_id.exists' => 'Sasaran strategis yang dipilih tidak valid.',
            'kode_program.required' => 'Kode program wajib diisi.',
            'kode_program.unique' => 'Kode program sudah digunakan di instansi ini.',
            'kode_program.max' => 'Kode program maksimal :max karakter.',
            'nama_program.required' => 'Nama program wajib diisi.',
            'nama_program.max' => 'Nama program maksimal :max karakter.',
            'deskripsi.max' => 'Deskripsi maksimal :max karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus salah satu dari: draft, aktif, atau selesai.',
            'tahun_mulai.required' => 'Tahun mulai wajib diisi.',
            'tahun_mulai.integer' => 'Tahun mulai harus berupa angka.',
            'tahun_mulai.min' => 'Tahun mulai minimal tahun :min.',
            'tahun_mulai.max' => 'Tahun mulai maksimal tahun :max.',
            'tahun_selesai.required' => 'Tahun selesai wajib diisi.',
            'tahun_selesai.integer' => 'Tahun selesai harus berupa angka.',
            'tahun_selesai.min' => 'Tahun selesai minimal tahun :min.',
            'tahun_selesai.max' => 'Tahun selesai maksimal tahun :max.',
            'tahun_selesai.gte' => 'Tahun selesai harus sama atau setelah tahun mulai.',
            'pagu_anggaran.numeric' => 'Pagu anggaran harus berupa angka.',
            'pagu_anggaran.min' => 'Pagu anggaran tidak boleh negatif.',
            'output.max' => 'Output maksimal :max karakter.',
            'sasaran.max' => 'Sasaran maksimal :max karakter.',
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
            'instansi_id' => 'Instansi',
            'sasaran_strategis_id' => 'Sasaran Strategis',
            'kode_program' => 'Kode Program',
            'nama_program' => 'Nama Program',
            'deskripsi' => 'Deskripsi',
            'status' => 'Status',
            'tahun_mulai' => 'Tahun Mulai',
            'tahun_selesai' => 'Tahun Selesai',
            'pagu_anggaran' => 'Pagu Anggaran',
            'output' => 'Output',
            'sasaran' => 'Sasaran',
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
        // Trim string fields
        $this->merge([
            'kode_program' => $this->input('kode_program') ? trim($this->input('kode_program')) : null,
            'nama_program' => $this->input('nama_program') ? trim($this->input('nama_program')) : null,
            'output' => $this->input('output') ? trim($this->input('output')) : null,
            'sasaran' => $this->input('sasaran') ? trim($this->input('sasaran')) : null,
        ]);
    }
}
