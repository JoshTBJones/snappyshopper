<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Store;
use App\Models\Organisation;
use App\Models\ApiKey;
use App\Models\Postcode;
use App\Models\Category;
class StoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that store endpoints require API key authentication.
     */
    public function test_store_endpoints_require_api_key(): void
    {
        // Test GET /api/stores
        $response = $this->getJson('/api/stores');
        $response->assertStatus(401)
                ->assertJson(['message' => 'API key is missing']);

        // Test GET /api/stores/{id} 
        $response = $this->getJson('/api/stores/1');
        $response->assertStatus(401)
                ->assertJson(['message' => 'API key is missing']);

        // Test POST /api/stores
        $response = $this->postJson('/api/stores', []);
        $response->assertStatus(401)
                ->assertJson(['message' => 'API key is missing']);

        // Test PUT /api/stores/{id}
        $response = $this->putJson('/api/stores/1', []);
        $response->assertStatus(401)
                ->assertJson(['message' => 'API key is missing']);

        // Test DELETE /api/stores/{id}
        $response = $this->deleteJson('/api/stores/1');
        $response->assertStatus(401)
                ->assertJson(['message' => 'API key is missing']);
    }

    /**
     * Test listing all stores.
     */
    public function test_can_list_stores(): void
    {
        // Create an API key
        $key = fake()->uuid();
        $organisation = Organisation::factory()->create();
        ApiKey::factory()->create(['key' => hash('sha256', $key), 'organisation_id' => $organisation->id]);

        // Create some test stores
        $store1 = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Test Store 1'
        ]);
        $store2 = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'name' => 'Test Store 2'
        ]);

        // Make request with API key
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->getJson('/api/stores');

        // Assert response
        $response->assertStatus(200)
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'uuid',
                            'name',
                            'organisation_uuid',
                            'latitude',
                            'longitude',
                            'max_delivery_distance',
                            'open'
                        ]
                    ]
                ])
                ->assertJsonFragment([
                    'name' => 'Test Store 1'
                ])
                ->assertJsonFragment([
                    'name' => 'Test Store 2'
                ]);
    }

    /**
     * Test creating a store.
     */
    public function test_can_create_store(): void
    {
        // Create an API key
        $key = fake()->uuid();
        $organisation = Organisation::factory()->create();
        ApiKey::factory()->create(['key' => hash('sha256', $key), 'organisation_id' => $organisation->id]);

        // Create a postcode
        $postcode = Postcode::factory()->create();
        // Create a category
        $category = Category::factory()->create();

        // Create a store
        $storeData = [
            'postcode' => $postcode->postcode,
            'name' => 'Test Store',
            'open' => 1,
            'max_delivery_distance' => 10,
            'category_ids' => [$category->id]
        ];

        // Make request with API key
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->postJson('/api/stores', $storeData);

        // Assert response
        $response->assertStatus(201)
                ->assertJson(['message' => 'Store created successfully.']);

        // Assert store was created in database
        $this->assertDatabaseHas('stores', [
            'name' => 'Test Store',
            'organisation_id' => $organisation->id,
            'postcode_id' => $postcode->id
        ]);
    }

    /**
     * Test deleting a store.
     */
    public function test_can_delete_store(): void
    {
        // Create an API key
        $key = fake()->uuid();
        $organisation = Organisation::factory()->create();
        ApiKey::factory()->create(['key' => hash('sha256', $key), 'organisation_id' => $organisation->id]);

        // Create a store
        $store = Store::factory()->create([
            'organisation_id' => $organisation->id
        ]);

        // Make delete request with API key
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->deleteJson('/api/stores/' . $store->uuid);

        // Assert response
        $response->assertStatus(204);

        // Assert store was deleted from database
        $this->assertSoftDeleted('stores', [
            'id' => $store->id
        ]);
    }

    /**
     * Test getting stores that deliver to a specific postcode.
     */
    public function test_can_get_stores_near_postcode(): void
    {
        // Create an API key
        $key = fake()->uuid();
        $organisation = Organisation::factory()->create();
        ApiKey::factory()->create(['key' => hash('sha256', $key), 'organisation_id' => $organisation->id]);

        // Create a postcode in London
        $londonPostcode = Postcode::factory()->create([
            'postcode' => 'SW1A 1AA',
            'latitude' => 51.5074,
            'longitude' => -0.1278
        ]);

        // Create a store within delivery distance
        $nearbyStore = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'postcode_id' => $londonPostcode->id,
            'latitude' => $londonPostcode->latitude,
            'longitude' => $londonPostcode->longitude,
            'max_delivery_distance' => 50 // 50km delivery radius
        ]);

        // Create a postcode in Manchester (too far from London store)
        $manchesterPostcode = Postcode::factory()->create([
            'postcode' => 'M1 1AE',
            'latitude' => 53.4808,
            'longitude' => -2.2426
        ]);

        // Create a store too far to deliver
        $farStore = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'postcode_id' => $manchesterPostcode->id,
            'latitude' => $manchesterPostcode->latitude,
            'longitude' => $manchesterPostcode->longitude,
            'max_delivery_distance' => 50 // 50km delivery radius
        ]);

        // Make request with API key to get stores near London postcode
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->getJson('/api/stores/delivering/' . $londonPostcode->postcode);

        // Assert response
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data') // Should only return the nearby store
                ->assertJsonPath('data.0.uuid', $nearbyStore->uuid);

        // Make request for Manchester postcode
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->getJson('/api/stores/delivering/' . $manchesterPostcode->postcode);

        // Assert response
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data') // Should only return Manchester store
                ->assertJsonPath('data.0.uuid', $farStore->uuid);
    }

    /**
     * Test getting stores near a postcode with a custom radius parameter.
     */
    public function testGetStoresNearPostcodeWithRadius()
    {
        // Create an API key
        $key = fake()->uuid();
        $organisation = Organisation::factory()->create();
        ApiKey::factory()->create(['key' => hash('sha256', $key), 'organisation_id' => $organisation->id]);

        // Create a postcode in London
        $londonPostcode = Postcode::factory()->create([
            'postcode' => 'SW1A1AA',
            'latitude' => 51.5074,
            'longitude' => -0.1278
        ]);

        // Create a store in London
        $londonStore = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'postcode_id' => $londonPostcode->id,
            'latitude' => $londonPostcode->latitude,
            'longitude' => $londonPostcode->longitude,
            'max_delivery_distance' => 300 // 300km delivery radius
        ]);

        // Create a postcode in Birmingham (~160km from London)
        $birminghamPostcode = Postcode::factory()->create([
            'postcode' => 'B11AA',
            'latitude' => 52.4862,
            'longitude' => -1.8904
        ]);

        // Create a store in Birmingham
        $birminghamStore = Store::factory()->create([
            'organisation_id' => $organisation->id,
            'postcode_id' => $birminghamPostcode->id,
            'latitude' => $birminghamPostcode->latitude,
            'longitude' => $birminghamPostcode->longitude,
            'max_delivery_distance' => 300
        ]);

        // Test with 100km radius - should only return London store
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->getJson('/api/stores/near/' . $londonPostcode->postcode . '?radius=100');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.uuid', $londonStore->uuid);

        // Test with 200km radius - should return both stores
        $response = $this->withHeader('Authorization', "Bearer " . $key)
                        ->getJson('/api/stores/near/' . $londonPostcode->postcode . '?radius=200');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');

        // Verify stores are returned in order of distance
        $stores = $response->json('data');
        $this->assertEquals($londonStore->uuid, $stores[0]['uuid']); // London store should be first
        $this->assertEquals($birminghamStore->uuid, $stores[1]['uuid']); // Birmingham store should be second
    }
}