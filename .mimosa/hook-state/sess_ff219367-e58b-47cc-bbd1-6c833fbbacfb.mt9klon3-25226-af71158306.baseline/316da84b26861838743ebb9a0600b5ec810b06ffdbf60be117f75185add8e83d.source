<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update User Roles Request
 *
 * Validates role assignment for a user.
 */
class UpdateRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('assignRoles', \App\Models\User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
            'roles.array' => 'Roles harus berupa array.',
            'roles.*.exists' => 'Role yang dipilih tidak valid.',
        ];
    }
}
