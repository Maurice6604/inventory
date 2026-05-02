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
        $products = [
            'Wireless Mouse', 'Mechanical Keyboard', 'Office Chair', 'Monitor Stand', 'Business Laptop', 
            'Noise Cancelling Headphones', 'Coffee Maker', 'Laser Printer', 'External Hard Drive', 'Webcam', 
            'USB Microphone', 'USB-C Hub', 'HDMI Cable', 'Standing Desk', 'Ergonomic Mouse', 
            'Tablet Computer', 'Business Smartphone', 'Power Bank 10000mAh', 'Fast Charger', 'Bluetooth Speaker', 
            'Gigabit Router', 'Network Switch', 'Extension Cord', 'Surge Protector', 'LED Desk Lamp',
            'Paper Shredder', 'Whiteboard', 'Filing Cabinet', 'Projector', 'Security Camera'
        ];
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => $this->faker->unique()->randomElement($products),
            'sku' => strtoupper($this->faker->unique()->lexify('PROD-????-') . $this->faker->numerify('####')),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'pack']),
            'description' => $this->faker->sentence(8),
            'cost_price' => $cost,
            'selling_price' => $cost * 1.5,
            'minimum_stock_level' => $this->faker->numberBetween(5, 50),
            'is_active' => true,
            'created_by' => User::inRandomOrder()->first()?->id,
        ];
    }
}
