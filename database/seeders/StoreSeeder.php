<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Store;
use App\Models\Organisation;
use App\Models\Postcode;
class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::first();
        $postcode = Postcode::first();

        $store = Store::create([
            'name' => 'Test Store',
            'organisation_id' => $organisation->id,
            'postcode_id' => $postcode->id,
            'open' => true,
            'max_delivery_distance' => 100, // 100km
        ]);

        $store->categories()->attach([1, 2, 3, 4]);
    }
}
