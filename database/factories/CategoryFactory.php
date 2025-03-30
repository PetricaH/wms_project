<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Configure the model factory to create a subcategory.
     *
     * @param int|null $parentId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function asSubcategory($parentId = null)
    {
        return $this->state(function (array $attributes) use ($parentId) {
            return [
                'parent_id' => $parentId ?? Category::factory(),
            ];
        });
    }

    /**
     * Indicate that the category is inactive.
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