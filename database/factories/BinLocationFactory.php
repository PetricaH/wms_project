<?php

namespace Database\Factories;

use App\Models\BinLocation;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BinLocation>
 */
class BinLocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BinLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $aisle = $this->faker->randomLetter();
        $bay = $this->faker->numberBetween(1, 99);
        $level = $this->faker->numberBetween(1, 5);
        
        return [
            'zone_id' => Zone::factory(),
            'name' => "Location {$aisle}{$bay}-{$level}",
            'code' => "{$aisle}{$bay}-{$level}",
            'position' => [
                'aisle' => $aisle,
                'bay' => $bay,
                'level' => $level,
            ],
            'capacity' => $this->faker->randomFloat(2, 1, 1000),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the bin location is inactive.
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