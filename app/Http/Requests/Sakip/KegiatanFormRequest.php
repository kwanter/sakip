<?php

namespace App\Http\Requests\Sakip;

use App\Constants\ValidationRules;
use App\Constants\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kegiatan Form Request
 *
 * Handles validation for Kegiatan (Activity) create and update operations.
 * Eliminates duplicate validation logic from controllers.
 *
 * @package App\Http\Requests\Sakip
 */
class KegiatanFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Policy-based authorization will be handled by middleware
        // This allows for flexible authorization strategies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $kegiatanId = $this->route('kegiatan')?->id;
        $programId = $this->input('program_id');

        return [
            'program_id' => [
                'required',
                'string',
                'exists:programs,id',
            ],
            'kode_kegiatan' => [
                'required',
                'string',
                'max:' . ValidationRules::CODE_MAX_LENGTH,
                Rule::unique('kegiatans', 'kode_kegiatan')->ignore($kegiatanId),
            ],
            'nama_kegiatan' => [
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
            'target_anggaran' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'realisasi_anggaran' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'mulai' => [
                'nullable',
                'date',
                'before:selesai',
            ],
            'selesai' => [
                'nullable',
                'date',
                'after:mulai',
            ],
            'penanggung_jawab' => [
                'nullable',
                'string',
                'max:' . ValidationRules::SHORT_TEXT_MAX_LENGTH,
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
            'program_id.required' => 'Program wajib dipilih.',
            'program_id.exists' => 'Program yang dipilih tidak valid.',
            'kode_kegiatan.required' => 'Kode kegiatan wajib diisi.',
            'kode_kegiatan.unique' => 'Kode kegiatan sudah digunakan.',
            'kode_kegiatan.max' => 'Kode kegiatan maksimal :max karakter.',
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'nama_kegiatan.max' => 'Nama kegiatan maksimal :max karakter.',
            'deskripsi.max' => 'Deskripsi maksimal :max karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus salah satu dari: draft, aktif, atau selesai.',
            'target_anggaran.numeric' => 'Target anggaran harus berupa angka.',
            'target_anggaran.min' => 'Target anggaran tidak boleh negatif.',
            'realisasi_anggaran.numeric' => 'Realisasi anggaran harus berupa angka.',
            'realisasi_anggaran.min' => 'Realisasi anggaran tidak boleh negatif.',
            'mulai.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'mulai.before' => 'Tanggal mulai harus sebelum tanggal selesai.',
            'selesai.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'penanggung_jawab.max' => 'Nama penanggung jawab maksimal :max karakter.',
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
            'program_id' => 'Program',
            'kode_kegiatan' => 'Kode Kegiatan',
            'nama_kegiatan' => 'Nama Kegiatan',
            'deskripsi' => 'Deskripsi',
            'status' => 'Status',
            'target_anggaran' => 'Target Anggaran',
            'realisasi_anggaran' => 'Realisasi Anggaran',
            'mulai' => 'Tanggal Mulai',
            'selesai' => 'Tanggal Selesai',
            'penanggung_jawab' => 'Penanggung Jawab',
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
            'kode_kegiatan' => $this->input('kode_kegiatan') ? trim($this->input('kode_kegiatan')) : null,
            'nama_kegiatan' => $this->input('nama_kegiatan') ? trim($this->input('nama_kegiatan')) : null,
            'penanggung_jawab' => $this->input('penanggung_jawab') ? trim($this->input('penanggung_jawab')) : null,
        ]);
    }
}
