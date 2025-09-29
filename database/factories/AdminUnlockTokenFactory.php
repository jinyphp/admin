<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\Models\AdminUnlockToken;
use Jiny\Admin\Models\User;
use Illuminate\Support\Str;

/**
 * 관리자 계정 잠금 해제 토큰 팩토리
 * 
 * 테스트를 위한 잠금 해제 토큰 데이터를 생성합니다.
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jiny\Admin\App\Models\AdminUnlockToken>
 */
class AdminUnlockTokenFactory extends Factory
{
    /**
     * 모델 이름
     *
     * @var string
     */
    protected $model = AdminUnlockToken::class;

    /**
     * 모델의 기본 상태 정의
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rawToken = Str::random(64);
        
        return [
            'user_id' => User::factory(),
            'token' => hash('sha256', $rawToken),
            'expires_at' => now()->addMinutes(60),
            'used_at' => null,
            'expired_at' => null,
            'attempts' => 0,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * 사용된 토큰 상태
     *
     * @return static
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
        ]);
    }

    /**
     * 만료된 토큰 상태
     *
     * @return static
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(1),
        ]);
    }

    /**
     * 수동으로 만료된 토큰 상태
     *
     * @return static
     */
    public function manuallyExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expired_at' => now(),
        ]);
    }

    /**
     * 최대 시도 횟수 초과 상태
     *
     * @return static
     */
    public function maxAttempts(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempts' => 5,
            'used_at' => now(),
        ]);
    }

    /**
     * 특정 사용자를 위한 토큰
     *
     * @param User $user
     * @return static
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}