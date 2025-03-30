<?php

namespace Database\Factories;

use App\Models\BinLocation;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InventoryMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementType = $this->faker->randomElement([
            InventoryMovement::TYPE_RECEIVE,
            InventoryMovement::TYPE_TRANSFER,
            InventoryMovement::TYPE_PICK,
            InventoryMovement::TYPE_ADJUST,
            InventoryMovement::TYPE_RETURN,
        ]);
        
        $fromLocation = null;
        $toLocation = null;
        
        if ($movementType === InventoryMovement::TYPE_RECEIVE) {
            $toLocation = BinLocation::factory()->create();
        } elseif ($movementType === InventoryMovement::TYPE_PICK) {
            $fromLocation = BinLocation::factory()->create();
        } elseif ($movementType === InventoryMovement::TYPE_TRANSFER) {
            $fromLocation = BinLocation::factory()->create();
            $toLocation = BinLocation::factory()->create();
        }
        
        return [
            'reference_type' => $this->faker->optional()->randomElement(['order', 'transfer', 'adjustment']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
            'product_id' => Product::factory(),
            'from_location_id' => $fromLocation?->id,
            'to_location_id' => $toLocation?->id,
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_of_measure' => $this->faker->randomElement(['EA', 'CS', 'BX', 'PK', 'KG', 'LB']),
            'lot_number' => $this->faker->optional()->regexify('LOT[0-9]{6}'),
            'batch_number' => $this->faker->optional()->regexify('BATCH[0-9]{4}'),
            'movement_type' => $movementType,
            'fifo_layers' => null,
            'reason' => $this->faker->sentence(),
            'performed_by' => User::factory(),
        ];
    }

    /**
     * Configure the model factory for a receive movement.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function receive()
    {
        return $this->state(function (array $attributes) {
            return [
                'movement_type' => InventoryMovement::TYPE_RECEIVE,
                'from_location_id' => null,
                'to_location_id' => BinLocation::factory(),
                'reason' => 'Goods receipt',
            ];
        });
    }

    /**
     * Configure the model factory for a transfer movement.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function transfer()
    {
        return $this->state(function (array $attributes) {
            return [
                'movement_type' => InventoryMovement::TYPE_TRANSFER,
                'from_location_id' => BinLocation::factory(),
                'to_location_id' => BinLocation::factory(),
                'reason' => 'Location transfer',
            ];
        });
    }

    /**
     * Configure the model factory for a pick movement.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pick()
    {
        return $this->state(function (array $attributes) {
            return [
                'movement_type' => InventoryMovement::TYPE_PICK,
                'from_location_id' => BinLocation::factory(),
                'to_location_id' => null,
                'reference_type' => 'order',
                'reference_id' => $this->faker->numberBetween(1, 1000),
                'reason' => 'Order picking',
            ];
        });
    }
}