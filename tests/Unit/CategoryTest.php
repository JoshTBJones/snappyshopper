<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test category creation.
     */
    public function testCategoryCreation()
    {
        Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'A test category description',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'A test category description',
        ]);
    }

    /**
     * Test category update.
     */
    public function testCategoryUpdate()
    {
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old description',
        ]);

        $category->update([
            'name' => 'New Name',
            'description' => 'New description',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'description' => 'New description',
        ]);
    }

    /**
     * Test category deletion.
     */
    public function testCategoryDeletion()
    {
        $category = Category::factory()->create();

        $category->delete();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test category has stores relationship.
     */
    public function testCategoryHasStores()
    {
        $category = Category::factory()->create();
        $store = Store::factory()->create();
        $category->stores()->attach($store);

        $this->assertTrue($category->stores->contains($store));
    }
} 