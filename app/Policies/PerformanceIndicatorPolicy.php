<?php

namespace App\Policies;

use App\Models\PerformanceIndicator;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PerformanceIndicatorPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        Log::info("PerformanceIndicatorPolicy@before: Checking ability '{$ability}' for User ID {$user->id}");
        if (Gate::forUser($user)->allows('isSuperAdmin')) {
            Log::info("PerformanceIndicatorPolicy@before: User ID {$user->id} is Super Admin, granting access.");
            return true;
        }
        Log::info("PerformanceIndicatorPolicy@before: User ID {$user->id} is not Super Admin, proceeding to policy methods.");
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $result = $user->hasPermissionTo('view any performance indicators');
        Log::info("PerformanceIndicatorPolicy@viewAny: User ID {$user->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        $result = $user->hasPermissionTo('view performance indicators') && $user->instansi_id === $performanceIndicator->instansi_id;
        Log::info("PerformanceIndicatorPolicy@view: User ID {$user->id}, Indicator ID {$performanceIndicator->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $result = $user->hasPermissionTo('create performance indicators');
        Log::info("PerformanceIndicatorPolicy@create: User ID {$user->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        $result = $user->hasPermissionTo('update performance indicators') && $user->instansi_id === $performanceIndicator->instansi_id;
        Log::info("PerformanceIndicatorPolicy@update: User ID {$user->id}, Indicator ID {$performanceIndicator->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        $result = $user->hasPermissionTo('delete performance indicators') && $user->instansi_id === $performanceIndicator->instansi_id;
        Log::info("PerformanceIndicatorPolicy@delete: User ID {$user->id}, Indicator ID {$performanceIndicator->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        $result = $user->hasPermissionTo('restore performance indicators') && $user->instansi_id === $performanceIndicator->instansi_id;
        Log::info("PerformanceIndicatorPolicy@restore: User ID {$user->id}, Indicator ID {$performanceIndicator->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        $result = $user->hasPermissionTo('force delete performance indicators') && $user->instansi_id === $performanceIndicator->instansi_id;
        Log::info("PerformanceIndicatorPolicy@forceDelete: User ID {$user->id}, Indicator ID {$performanceIndicator->id} - Result: " . ($result ? 'true' : 'false'));
        return $result;
    }
}
