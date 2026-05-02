<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Electronics', 'Office Supplies', 'Furniture', 'Clothing', 'Tools', 'Appliances', 'Hardware', 'Software', 'Accessories', 'Automotive', 'Kitchenware', 'Stationery', 'Networking', 'Storage', 'Peripherals'];
        $descriptions = [
            'Essential items for daily operations and productivity.',
            'High quality materials built to last.',
            'Premium products sourced from reliable vendors.',
            'Affordable and durable everyday items.',
            'Specialized equipment for professional use.',
            'Everything you need to keep things running smoothly.',
            'A curated selection of our best-selling items.'
        ];
        return [
            'name' => $this->faker->unique()->randomElement($categories),
            'description' => $this->faker->randomElement($descriptions),
            'is_active' => true,
        ];
    }
}
