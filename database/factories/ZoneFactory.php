<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Zone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'name' => $this->faker->randomElement(['Receiving', 'Shipping', 'Storage', 'Picking', 'Packing', 'Bulk', 'HazMat']) . ' Zone',
            'code' => $this->faker->unique()->regexify('[A-Z]{1}[0-9]{2}'),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the zone is inactive.
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