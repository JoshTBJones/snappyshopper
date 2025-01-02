<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Store extends Model
{
    use SoftDeletes, HasUuid, HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'uid',
        'organisation_id',
        'postcode_id',
        'name',
        'open',
        'max_delivery_distance',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        // 'coordinates',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'max_delivery_distance' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'stores_categories');
    }

    public function postcode()
    {
        return $this->belongsTo(Postcode::class);
    }

    public static function near(Postcode $postcode, int $radius = null)
    {
        return self::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", 
                [$postcode->latitude, $postcode->longitude, $postcode->latitude])
            ->when($radius, function ($query) use ($radius) {
                // Filter by specified radius
                $query->havingRaw('distance <= ?', [$radius]);
            }, function ($query) {
                // Default to filtering by max_delivery_distance
                $query->havingRaw('distance <= max_delivery_distance');
            })
            ->orderBy('distance')
            ->get();
    }

}
