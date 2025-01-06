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

    /**
     * Find locations near a given postcode within a specified radius or the default maximum delivery distance.
     *
     * This method calculates the distance between the provided postcode's latitude and longitude
     * and the locations stored in the database using the Haversine formula.
     *
     * @param Postcode $postcode The postcode object containing latitude and longitude coordinates.
     * @param int|null $radius Optional. The radius in kilometers to filter the results. If not provided,
     *                         the method will use the `max_delivery_distance` column from the database.
     *
     * @return \Illuminate\Support\Collection A collection of locations ordered by their distance from the given postcode.
     *
     * @throws \Exception If the query execution fails, it may throw an exception depending on the database connection.
     *
     * @example
     * $nearbyLocations = Location::near($postcode, 10); // Find locations within 10 km of the given postcode.
     * $defaultRadiusLocations = Location::near($postcode); // Find locations using the default maximum delivery distance.
     */
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
