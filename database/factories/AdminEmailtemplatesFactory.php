<?php

namespace Database\Factories;

use Jiny\Admin\Models\AdminEmailtemplates;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jiny\Admin\Models\AdminEmailtemplates>
 */
class AdminEmailtemplatesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminEmailtemplates::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(3),
            'subject' => $this->faker->sentence(6),
            'body' => $this->faker->paragraphs(3, true),
            'variables' => ['user_name', 'email', 'date', 'company_name'],
            'type' => $this->faker->randomElement(['html', 'text', 'markdown']),
            'category' => $this->faker->randomElement(['회원가입', '비밀번호 재설정', '공지사항', '마케팅']),
            'is_active' => $this->faker->boolean(80),
            'status' => $this->faker->boolean(80),
            'priority' => $this->faker->numberBetween(-5, 5),
            'from_name' => $this->faker->name(),
            'from_email' => $this->faker->safeEmail(),
            'reply_to' => $this->faker->safeEmail(),
            'description' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the emailtemplates is enabled.
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'status' => true,
        ]);
    }

    /**
     * Indicate that the emailtemplates is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'status' => false,
        ]);
    }
}