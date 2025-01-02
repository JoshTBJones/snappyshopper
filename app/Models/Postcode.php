<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\HasCoordinates;

class Postcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'postcode',
        'outcode',
        'incode',
        'latitude',
        'longitude',
        'country',
        'region',
        'district',
        'active'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
