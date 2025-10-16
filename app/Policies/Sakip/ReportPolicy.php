<?php

namespace App\Policies\Sakip;

use App\Models\User;
use App\Models\Report;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function update(User $user, Report $report): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->hasRole(['admin']);
    }

    public function restore(User $user, Report $report): bool
    {
        return $user->hasRole(['admin']);
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return $user->hasRole(['admin']);
    }
}