<?php

namespace App\Models\Scopes;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * WithStatus Scope
 *
 * Filters records by status value.
 * Eliminates repeated ->where('status', 'aktif') calls throughout the codebase.
 *
 * Usage:
 *   Model::where('status', 'aktif')->get();
 *   // Becomes:
 *   Model::withStatus(Status::ACTIVE)->get();
 *
 *   // Multiple statuses:
 *   Model::withAnyStatus([Status::ACTIVE, Status::DRAFT])->get();
 */
trait WithStatusScope
{
    /**
     * Scope a query to only include records with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status The status value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include records with any of the given statuses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $statuses Array of status values
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyStatus(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Scope a query to only include draft records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', Status::DRAFT);
    }

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::ACTIVE);
    }

    /**
     * Scope a query to only include completed records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', Status::COMPLETED);
    }

    /**
     * Scope a query to only include pending records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', Status::PENDING);
    }

    /**
     * Scope a query to only include submitted records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', Status::SUBMITTED);
    }

    /**
     * Scope a query to only include validated records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('status', Status::VALIDATED);
    }

    /**
     * Scope a query to only include approved records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', Status::APPROVED);
    }

    /**
     * Scope a query to only include rejected records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', Status::REJECTED);
    }

    /**
     * Scope a query to exclude records with specific statuses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $statuses Array of status values to exclude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutStatus(Builder $query, array $statuses): Builder
    {
        return $query->whereNotIn('status', $statuses);
    }
}
