<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Takeaway', 'description' => 'Takeaway food establishments'],
            ['name' => 'Shop', 'description' => 'General retail shops'],
            ['name' => 'Restaurant', 'description' => 'Sit-down restaurants'],
            ['name' => 'Cafe', 'description' => 'Coffee shops and cafes'],
            ['name' => 'Supermarket', 'description' => 'Large grocery stores'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
