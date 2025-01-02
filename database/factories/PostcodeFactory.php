<?php

namespace Database\Factories;

use App\Models\Postcode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Provider\Base;

class PostcodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Postcode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $postcode = $this->generateUkPostcode();

        $outcode = substr($postcode, 0, -3);
        $incode = substr($postcode, -3);
        // normalise the postcode
        $postcode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $postcode));

        return [
            'postcode' => $postcode,
            'outcode' => $outcode,
            'incode' => $incode,
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'country' => $this->faker->countryCode,
            'region' => $this->faker->state,
            'district' => $this->faker->city,
            'active' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    function generateUkPostcode(): string
    {
        // Define the characters used in various parts of the UK postcode
        $areaLetters = 'ABDEFGHJLNPQRSTUWXYZ'; // First letters
        $districtNumbers = '0123456789'; // Numbers for the district
        $subDistrictLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Optional letters in district
        $sectorNumbers = '0123456789'; // Numbers for the sector
        $unitLetters = 'ABDEFGHJLNPQRSTUWXYZ'; // Letters for the unit

        // Randomly pick the outward code (area and district)
        $area = $areaLetters[random_int(0, strlen($areaLetters) - 1)];
        $district = random_int(1, 9); // District must be non-zero

        // 20% chance of having a second letter in the outward district
        if (random_int(1, 100) <= 20) {
            $district .= $subDistrictLetters[random_int(0, strlen($subDistrictLetters) - 1)];
        }

        // Randomly pick the inward code (sector and unit)
        $sector = random_int(0, 9);
        $unit = $unitLetters[random_int(0, strlen($unitLetters) - 1)] .
                $unitLetters[random_int(0, strlen($unitLetters) - 1)];

        // Return the full postcode
        return sprintf('%s%d %d%s', $area, $district, $sector, $unit);
    }
}
