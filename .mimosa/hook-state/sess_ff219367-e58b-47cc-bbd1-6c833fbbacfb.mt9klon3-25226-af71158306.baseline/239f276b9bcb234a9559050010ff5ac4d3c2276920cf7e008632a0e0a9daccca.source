<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

/**
 * Search Scope
 *
 * Provides convenient methods for searching across multiple columns.
 * Eliminates repeated search query patterns.
 *
 * Usage:
 *   Model::search('keyword')->get();
 *   Model::searchIn(['name', 'description'], 'keyword')->get();
 */
trait SearchScope
{
    /**
     * Scope a query to search in default searchable columns.
     * Models can override $searchable property to customize.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $keyword The search keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        $model = $query->getModel();

        // Check if model has $searchable property
        if (property_exists($model, 'searchable')) {
            return $this->scopeSearchIn($query, $model->searchable, $keyword);
        }

        // Default search behavior - search in name column if exists
        if (null !== $model->getTable() && \Schema::hasColumn($model->getTable(), 'nama_' . strtolower(class_basename($model)))) {
            $column = 'nama_' . strtolower(class_basename($model));
            return $query->where($column, 'like', "%{$keyword}%");
        }

        return $query;
    }

    /**
     * Scope a query to search in specific columns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $columns Array of column names to search in
     * @param  string  $keyword The search keyword
     * @param  string  $boolean 'and' or 'or' (default: 'or')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchIn(Builder $query, array $columns, string $keyword, string $boolean = 'or'): Builder
    {
        $boolean = $boolean === 'and' ? 'where' : 'orWhere';

        return $query->where(function ($q) use ($columns, $keyword, $boolean) {
            $firstColumn = array_shift($columns);

            $q->where($firstColumn, 'like', "%{$keyword}%");

            foreach ($columns as $column) {
                $q->$boolean($column, 'like', "%{$keyword}%");
            }
        });
    }

    /**
     * Scope a query to search with exact match.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column Column to search in
     * @param  string  $keyword The search keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchExact(Builder $query, string $column, string $keyword): Builder
    {
        return $query->where($column, $keyword);
    }

    /**
     * Scope a query to search starting with a prefix.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column Column to search in
     * @param  string  $prefix The prefix to search for
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartsWith(Builder $query, string $column, string $prefix): Builder
    {
        return $query->where($column, 'like', "{$prefix}%");
    }

    /**
     * Scope a query to search ending with a suffix.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column Column to search in
     * @param  string  $suffix The suffix to search for
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEndsWith(Builder $query, string $column, string $suffix): Builder
    {
        return $query->where($column, 'like', "%{$suffix}");
    }
}
