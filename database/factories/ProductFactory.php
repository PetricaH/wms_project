<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => null,
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 1000),
            'cost' => $this->faker->randomFloat(2, 1, 500),
            'upc' => $this->faker->ean13(),
            'weight' => $this->faker->randomFloat(2, 0.1, 100),
            'dimensions' => [
                'length' => $this->faker->randomFloat(1, 1, 100),
                'width' => $this->faker->randomFloat(1, 1, 100),
                'height' => $this->faker->randomFloat(1, 1, 100),
            ],
            'attributes' => [
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'material' => $this->faker->word(),
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the product is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}