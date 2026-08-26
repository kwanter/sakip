<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Instansi multi-tenancy scope.
 *
 * SEMANTICS (immutable; see architecture-remediation-plan.md v3 P4):
 *   - No authenticated user (console, queue, seeder): no filter
 *   - Authenticated user with SUPER_ADMIN role: no filter
 *   - Any other authenticated user: WHERE instansi_id = user.instansi_id
 *     (null instansi sees nothing; default-deny)
 *
 * Escape hatch: $model->withoutInstansiScope()->get()
 */
class InstansiScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        /** @var \App\Models\User $user */
        if ($user->hasRole(\App\Constants\SystemRoles::SUPER_ADMIN)) {
            return;
        }

        $builder->where($model->getTable().'.instansi_id', $user->instansi_id);
    }
}