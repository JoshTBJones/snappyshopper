<?php

namespace Tests\Unit;

use App\Models\Organisation;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test organisation creation.
     */
    public function testOrganisationCreation()
    {
        $organisation = Organisation::factory()->create([
            'name' => 'Test Organisation',
        ]);

        $this->assertDatabaseHas('organisations', [
            'name' => 'Test Organisation',
        ]);

        $this->assertNotNull($organisation->uuid, 'UUID should be generated');
    }

    /**
     * Test organisation has stores relationship.
     */
    public function testOrganisationHasStores()
    {
        $organisation = Organisation::factory()->create();
        $store = Store::factory()->create(['organisation_id' => $organisation->id]);

        $this->assertTrue($organisation->stores->contains($store));
    }

    /**
     * Test organisation update.
     */
    public function testOrganisationUpdate()
    {
        $organisation = Organisation::factory()->create([
            'name' => 'Old Name',
        ]);

        $organisation->update(['name' => 'New Name']);

        $this->assertDatabaseHas('organisations', [
            'id' => $organisation->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test organisation deletion.
     */
    public function testOrganisationDeletion()
    {
        $organisation = Organisation::factory()->create();

        $organisation->delete();

        $this->assertSoftDeleted('organisations', [
            'id' => $organisation->id,
        ]);
    }
} 