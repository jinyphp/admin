<?php

namespace Database\Factories;

use Admin\admin\App\Models\AdminSmsProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Admin\admin\App\Models\AdminSmsProvider>
 */
class AdminSmsProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminSmsProvider::class;

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
     * Indicate that the sms_provider is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => true,
        ]);
    }

    /**
     * Indicate that the sms_provider is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable' => false,
        ]);
    }
}