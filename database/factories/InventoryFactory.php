<?php

namespace Database\Factories;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inventory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(10, 500);
        $reserved = $this->faker->numberBetween(0, $quantity);
        
        return [
            'product_id' => Product::factory(),
            'location_id' => BinLocation::factory(),
            'lot_number' => $this->faker->regexify('LOT[0-9]{6}'),
            'batch_number' => $this->faker->regexify('BATCH[0-9]{4}'),
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $quantity - $reserved, // This will be auto-calculated by the model
            'unit_of_measure' => $this->faker->randomElement(['EA', 'CS', 'BX', 'PK', 'KG', 'LB']),
            'expiry_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+2 years'),
            'received_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Set the inventory to have full available quantity (no reservations).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function fullyAvailable()
    {
        return $this->state(function (array $attributes) {
            return [
                'reserved_quantity' => 0,
            ];
        });
    }

    /**
     * Set the inventory to be fully reserved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function fullyReserved()
    {
        return $this->state(function (array $attributes) {
            return [
                'reserved_quantity' => $attributes['quantity'],
            ];
        });
    }

    /**
     * Set the inventory to be expired.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            ];
        });
    }

    /**
     * Set the inventory to have zero quantity.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function empty()
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => 0,
                'reserved_quantity' => 0,
            ];
        });
    }
}