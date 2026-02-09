<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ForYear Scope
 *
 * Filters records by a specific year for a given date column.
 * This eliminates repeated ->whereYear() calls throughout the codebase.
 *
 * Usage:
 *   Model::whereYear('created_at', 2024)->get();
 *   // Becomes:
 *   Model::forYear('created_at', 2024)->get();
 */
class ForYearScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // The scope is applied dynamically via the forYear() method on the model
        // This allows flexibility for different date columns
    }
}

/**
 * Trait to add forYear() scope to models
 *
 * This trait can be used by any model that needs year-based filtering.
 * It provides a fluent interface for year filtering on any date column.
 */
trait ForYear
{
    /**
     * Scope a query to only include records for a specific year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to filter on
     * @param  int  $year The year to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear(Builder $query, string $column, int $year): Builder
    {
        return $query->whereYear($column, $year);
    }

    /**
     * Scope a query to only include records for the current year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to filter on
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCurrentYear(Builder $query, string $column): Builder
    {
        return $query->whereYear($column, date('Y'));
    }

    /**
     * Scope a query to only include records within a year range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column The date column to filter on
     * @param  int  $startYear Start year
     * @param  int  $endYear End year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYearRange(Builder $query, string $column, int $startYear, int $endYear): Builder
    {
        return $query->whereYear($column, '>=', $startYear)
                    ->whereYear($column, '<=', $endYear);
    }
}
