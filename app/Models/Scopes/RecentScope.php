<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

/**
 * Recent Scope
 *
 * Provides convenient methods for ordering records by recency.
 * Eliminates repeated ->orderBy('created_at', 'desc') calls.
 *
 * Usage:
 *   Model::recent()->get();
 *   Model::latest()->get();
 *   Model::oldest()->get();
 */
trait RecentScope
{
    /**
     * Scope a query to order by recent records first.
     *
     * @param  \Illuminate\Database\\Eloquent\\Builder  $query
     * @param  string  $column The column to order by (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope a query to include only the latest records.
     * Alias for recent().
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The column to order by (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope a query to include the oldest records first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The column to order by (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOldest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'asc');
    }

    /**
     * Scope a query to include records from the last N days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days Number of days
     * @param  string  $column The date column to check (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromLastDays(Builder $query, int $days, string $column = 'created_at'): Builder
    {
        return $query->where($column, '>=', now()->subDays($days));
    }

    /**
     * Scope a query to include records from today.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to check (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromToday(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereDate($column, '>=', now()->toDateString());
    }

    /**
     * Scope a query to include records from this month.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to check (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromThisMonth(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereYear($column, now()->year)
                    ->whereMonth($column, now()->month);
    }

    /**
     * Scope a query to include records from this year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to check (default: created_at)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromThisYear(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereYear($column, now()->year);
    }
}
