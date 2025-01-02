<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait and automatically assign a UUID when creating the model.
     */
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid(); // Generate a UUID
            }
        });
    }

    /**
     * Indicate the key type is string.
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Scope to find a model by its UUID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $uuid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindByUuid($query, string $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
     * Static method to find a model by UUID.
     *
     * @param string $uuid
     * @return self|null
     */
    public static function findByUuid(string $uuid): ?self
    {
        return static::where('uuid', $uuid)->first();
    }
}
