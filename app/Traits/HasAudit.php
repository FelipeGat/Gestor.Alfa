<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    public function scopeWithTrashed(Builder $query): Builder
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed(Builder $query): Builder
    {
        return $query->onlyTrashed();
    }

    public function scopeWhereCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWhereUpdatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('updated_by', $userId);
    }
}
