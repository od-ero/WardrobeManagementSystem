<?php

namespace Database\Seeders;
use App\Models\WardrobeCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WardrobeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'tops'],
            ['name' => 'bottoms'],
            ['name' => 'shoes'],
            ['name' => 'Accessories'],
           
        ];

        foreach ( $categories as  $category) {
           WardrobeCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
