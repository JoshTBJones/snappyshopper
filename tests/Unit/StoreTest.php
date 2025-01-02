<?php

namespace Tests\Unit;

use App\Models\Store;
use App\Models\Organisation;
use App\Models\Category;
use App\Models\Postcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test store creation.
     */
    public function testStoreCreation()
    {
        $organisation = Organisation::factory()->create();
        $store = Store::factory()->create([
            'name' => 'Test Store',
            'organisation_id' => $organisation->id,
        ]);

        $this->assertDatabaseHas('stores', [
            'name' => 'Test Store',
            'organisation_id' => $organisation->id,
        ]);

        $this->assertNotNull($store->uuid, 'UUID should be generated');
    }

    /**
     * Test store update.
     */
    public function testStoreUpdate()
    {
        $store = Store::factory()->create([
            'name' => 'Old Name',
        ]);

        $store->update(['name' => 'New Name']);

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test store deletion.
     */
    public function testStoreDeletion()
    {
        $store = Store::factory()->create();

        $store->delete();

        $this->assertSoftDeleted('stores', [
            'id' => $store->id,
        ]);
    }

    /**
     * Test store belongs to organisation.
     */
    public function testStoreBelongsToOrganisation()
    {
        $organisation = Organisation::factory()->create();
        $store = Store::factory()->create(['organisation_id' => $organisation->id]);

        $this->assertTrue($store->organisation->is($organisation));
    }

    /**
     * Test store belongs to postcode.
     */
    public function testStoreBelongsToPostcode()
    {
        $postcode = Postcode::factory()->create();
        $store = Store::factory()->create(['postcode_id' => $postcode->id]);

        $this->assertTrue($store->postcode->is($postcode));
    }

    /**
     * Test store has categories relationship.
     */
    public function testStoreHasCategories()
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();
        $store->categories()->attach($category);

        $this->assertTrue($store->categories->contains($category));
    }

    /**
     * Test distance calculation between two stores.
     */
    public function testDistanceBetweenStores()
    {
        // Create two postcodes with known coordinates
        $postcode1 = Postcode::factory()->create([
            'latitude' => 51.5074, // London coordinates
            'longitude' => -0.1278
        ]);

        $postcode2 = Postcode::factory()->create([
            'latitude' => 53.4808, // Manchester coordinates  
            'longitude' => -2.2426
        ]);

        // Create stores at these postcodes
        $store1 = Store::factory()->create([
            'postcode_id' => $postcode1->id,
            'latitude' => $postcode1->latitude,
            'longitude' => $postcode1->longitude
        ]);

        $store2 = Store::factory()->create([
            'postcode_id' => $postcode2->id,
            'latitude' => $postcode2->latitude,
            'longitude' => $postcode2->longitude
        ]);

        // Get stores near the first store's postcode
        $nearbyStores = Store::near($postcode1, 500)->toArray();

        // The distance between London and Manchester is roughly 260km
        // So both stores should be found within 500km radius
        $this->assertCount(2, $nearbyStores);

        // First store should be closest to itself (distance near 0)
        $this->assertEquals($store1->id, $nearbyStores[0]['id']);
        $this->assertLessThan(1, $nearbyStores[0]['distance']);

        // Second store should be second closest
        $this->assertEquals($store2->id, $nearbyStores[1]['id']);
        $this->assertGreaterThan(200, $nearbyStores[1]['distance']);
        $this->assertLessThan(300, $nearbyStores[1]['distance']);
    }

    /**
     * Test distance calculation between a store and a postcode.
     */
    public function testDistanceToPostcode()
    {
        // Create a postcode with known coordinates
        $storePostcode = Postcode::factory()->create([
            'latitude' => 51.5074, // London coordinates
            'longitude' => -0.1278
        ]);

        // Create store at this postcode
        $store = Store::factory()->create([
            'postcode_id' => $storePostcode->id,
            'latitude' => $storePostcode->latitude,
            'longitude' => $storePostcode->longitude,
            'max_delivery_distance' => 300 // 300km delivery radius
        ]);

        // Create another postcode to test distance to
        $testPostcode = Postcode::factory()->create([
            'latitude' => 53.4808, // Manchester coordinates
            'longitude' => -2.2426
        ]);

        // Get stores near the test postcode
        $nearbyStores = Store::near($testPostcode)->toArray();

        // Store should be found since Manchester is ~260km from London
        // and max_delivery_distance is 300km
        $this->assertCount(1, $nearbyStores);
        $this->assertEquals($store->id, $nearbyStores[0]['id']);

        // Distance should be between 200-300km
        $this->assertGreaterThan(200, $nearbyStores[0]['distance']);
        $this->assertLessThan(300, $nearbyStores[0]['distance']);

        // Create a postcode that's too far away
        $farPostcode = Postcode::factory()->create([
            'latitude' => 55.9533, // Edinburgh coordinates (~530km from London)
            'longitude' => -3.1883
        ]);

        // No stores should be found near Edinburgh as it's outside delivery radius
        $farStores = Store::near($farPostcode)->toArray();
        $this->assertEmpty($farStores);
    }
}