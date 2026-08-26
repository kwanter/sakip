<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store User Request
 *
 * Validates user creation data with proper password requirements.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'instansi_id' => 'nullable|uuid|exists:instansis,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung huruf kapital, huruf kecil, angka, dan simbol (@$!%*?&).',
            'email.unique' => 'Email sudah terdaftar.',
            'instansi_id.exists' => 'Instansi tidak ditemukan.',
            'roles.*.exists' => 'Role yang dipilih tidak valid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'password',
            'instansi_id' => 'instansi',
            'roles' => 'peran',
        ];
    }
}
