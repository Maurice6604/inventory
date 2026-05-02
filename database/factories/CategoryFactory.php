<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Electronics', 'Office Supplies', 'Furniture', 'Clothing', 'Tools', 'Appliances', 'Hardware', 'Software', 'Accessories', 'Automotive', 'Kitchenware', 'Stationery', 'Networking', 'Storage', 'Peripherals'];
        return [
            'name' => $this->faker->unique()->randomElement($categories),
            'description' => $this->faker->sentence(6),
            'is_active' => true,
        ];
    }
}
