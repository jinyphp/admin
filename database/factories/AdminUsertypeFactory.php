<?php

namespace Database\Factories;

use User\user\App\Models\AdminType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\User\user\App\Models\AdminType>
 */
class AdminTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'enable' => $this->faker->boolean(80), // 80% chance of being enabled
            'pos' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the type is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => true,
        ]);
    }

    /**
     * Indicate that the type is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => false,
        ]);
    }
}