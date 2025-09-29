<?php

namespace Database\Factories;

use Admin\admin\App\Models\AdminSessions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Admin\admin\App\Models\AdminSessions>
 */
class AdminSessionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminSessions::class;

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
     * Indicate that the sessions is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => true,
        ]);
    }

    /**
     * Indicate that the sessions is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => false,
        ]);
    }
}