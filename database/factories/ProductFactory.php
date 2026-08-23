<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = ucfirst(fake()->words(3, true));

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 99999),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock' => fake()->numberBetween(0, 20),
            'condition' => fake()->randomElement(['neuf', 'comme_neuf', 'bon_etat', 'usage']),
            'brand' => fake()->randomElement(['Pokemon', 'Nintendo', 'PlayStation', 'Funko', 'Marvel', null]),
            'rarity' => fake()->randomElement(['commune', 'peu commune', 'rare', 'holo', 'secrete', null]),
            'status' => 'active',
        ];
    }
}
