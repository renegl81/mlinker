<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasDynamicFilters
{
    public function scopeApplyFilters(Builder $query, Request $request): Builder
    {
        foreach (static::getFilterableFields() as $field => $operator) {
            if (!$request->filled($field)) {
                continue;
            }

            match ($operator) {
                'like' => $query->where($field, 'like', '%' . $request->input($field) . '%'),
                '=' => $query->where($field, $request->input($field)),
                'in' => $query->whereIn($field, (array) $request->input($field)),
                default => null,
            };
        }

        return $query;
    }

    abstract public static function getFilterableFields(): array;
}
