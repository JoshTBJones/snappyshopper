<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Organisation;
use App\Models\Postcode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $postcode = Postcode::factory()->create();

        return [
            'uuid' => (string) Str::uuid(),
            'organisation_id' => Organisation::factory(),
            'postcode_id' => $postcode->id,
            'name' => $this->faker->company,
            'open' => $this->faker->boolean,
            'max_delivery_distance' => $this->faker->numberBetween(1, 100), // Distance in km
            'latitude' => $postcode->latitude,
            'longitude' => $postcode->longitude,
        ];
    }
} 