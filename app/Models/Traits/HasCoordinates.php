<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

trait HasCoordinates
{

    /**
     * Boot the HasCoordinates trait.
     * 
     * Automatically sets the coordinates field as a POINT geometry
     * when a model with latitude and longitude is saved.
     *
     * @return void
     */
    public static function bootHasCoordinates(): void
    {
        static::saving(function ($model) {
            if ($model->latitude && $model->longitude) {
                // $model->coordinates = DB::raw("ST_GeomFromText('POINT({$model->longitude} {$model->latitude})', 4326)");
            }
        });
    }
}
