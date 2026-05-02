<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = $this->faker->randomFloat(2, 5, 100);
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => ucfirst($this->faker->unique()->words(3, true)),
            'sku' => strtoupper($this->faker->unique()->lexify('PROD-????-') . $this->faker->numerify('####')),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'pack']),
            'description' => $this->faker->paragraph(),
            'cost_price' => $cost,
            'selling_price' => $cost * 1.5,
            'minimum_stock_level' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
