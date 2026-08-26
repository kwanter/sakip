<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update System Settings Request
 *
 * Validates system settings update with type-based validation.
 */
class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'required',
            'settings.*.type' => 'required|string|in:string,integer,float,boolean,array,json',
            'settings.*.description' => 'nullable|string|max:500',
        ];

        // Add specific validation for known settings
        foreach ($this->input('settings', []) as $key => $setting) {
            $settingKey = $setting['key'] ?? '';

            if ($settingKey === 'app.name') {
                $rules['settings.' . $key . '.value'] = 'sometimes|required|string|max:150';
            }
            if ($settingKey === 'app.description') {
                $rules['settings.' . $key . '.value'] = 'sometimes|nullable|string|max:500';
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'settings.required' => 'Settings wajib diisi.',
            'settings.array' => 'Settings harus berupa array.',
            'settings.*.key.required' => 'Key setting wajib diisi.',
            'settings.*.type.required' => 'Tipe setting wajib diisi.',
            'settings.*.type.in' => 'Tipe setting harus: string, integer, float, boolean, array, atau json.',
        ];
    }
}
