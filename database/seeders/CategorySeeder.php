<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rice & Noodles', 'slug' => 'rice-noodles', 'description' => 'Delicious rice and noodle dishes', 'image' => 'https://images.unsplash.com/photo-1512058560550-42749969a6b0?w=800', 'sort_order' => 1],
            ['name' => 'Burgers & Sandwiches', 'slug' => 'burgers-sandwiches', 'description' => 'Tasty burgers and sandwiches', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800', 'sort_order' => 2],
            ['name' => 'Beverages', 'slug' => 'beverages', 'description' => 'Refreshing drinks', 'image' => 'https://images.unsplash.com/photo-1544145945-f904253d0c7e?w=800', 'sort_order' => 3],
            ['name' => 'Snacks', 'slug' => 'snacks', 'description' => 'Quick bites and snacks', 'image' => 'https://images.unsplash.com/photo-1599490659223-930b449978ca?w=800', 'sort_order' => 4],
            ['name' => 'Desserts', 'slug' => 'desserts', 'description' => 'Sweet treats', 'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=800', 'sort_order' => 5],
            ['name' => 'Local Favorites', 'slug' => 'local-favorites', 'description' => 'Popular local dishes', 'image' => 'https://images.unsplash.com/photo-1596797038583-18a68a637311?w=800', 'sort_order' => 6],
            ['name' => 'Sushi & Sashimi', 'slug' => 'sushi-sashimi', 'description' => 'Fresh Japanese seafood', 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=800', 'sort_order' => 7],
            ['name' => 'Healthy Salads', 'slug' => 'healthy-salads', 'description' => 'Organic greens and bowls', 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800', 'sort_order' => 8],
            ['name' => 'Indian Curries', 'slug' => 'indian-curries', 'description' => 'Aromatic traditional spices', 'image' => 'https://images.unsplash.com/photo-1585937421612-7110052026ae?w=800', 'sort_order' => 9],
            ['name' => 'Grilled Seafood', 'slug' => 'grilled-seafood', 'description' => 'Fresh catch from the ocean', 'image' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=800', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}