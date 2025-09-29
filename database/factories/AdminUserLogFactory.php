<?php

namespace Jiny\Admin\Database\Factories;

use Jiny\Admin\Models\AdminUserLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jiny\Admin\Models\AdminUserLog>
 */
class AdminUserLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminUserLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'email' => $this->faker->email(),
            'name' => $this->faker->name(),
            'action' => $this->faker->randomElement(['login', 'logout', 'password_changed', 'profile_updated', 'settings_changed']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'details' => json_encode([
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'platform' => $this->faker->randomElement(['Windows', 'Mac', 'Linux', 'iOS', 'Android']),
            ]),
            'session_id' => Str::random(40),
            'logged_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'two_factor_used' => $this->faker->boolean(30),
            'two_factor_required' => $this->faker->boolean(50),
            'two_factor_method' => $this->faker->randomElement(['totp', 'sms', 'email', null]),
            'two_factor_verified_at' => function (array $attributes) {
                return $attributes['two_factor_used'] ? $this->faker->dateTimeBetween('-30 days', 'now') : null;
            },
            'two_factor_attempts' => $this->faker->numberBetween(0, 3),
        ];
    }

    /**
     * Indicate that the log is for login action.
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'two_factor_used' => true,
        ]);
    }

    /**
     * Indicate that the log is for logout action.
     */
    public function logout(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'logout',
            'two_factor_used' => false,
        ]);
    }
}