<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Tenant binding rule for planning writes: ordinary actors may only target
 * their own instansi; a viewer without an instansi is rejected outright
 * (default-deny); Super Admin keeps explicit cross-instansi authority.
 *
 * Replaces the Rule::in([$user?->instansi_id]) shape whose null case relied
 * on string-cast semantics (review STD-09).
 */
final class TenantRule
{
    /**
     * @return array<int, mixed>
     */
    public static function ownInstansi(?User $user): array
    {
        if ($user !== null && $user->isAdmin()) {
            return ['required', 'exists:instansis,id'];
        }

        $own = $user?->instansi_id;

        return [
            'required',
            'exists:instansis,id',
            Rule::in(array_filter([$own])),
            function (string $attribute, mixed $value, Closure $fail) use ($own) {
                if ($own === null || $value !== $own) {
                    $fail('Instansi tujuan harus sesuai dengan instansi Anda.');
                }
            },
        ];
    }
}
