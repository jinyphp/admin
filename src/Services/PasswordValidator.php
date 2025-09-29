<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\Hash;

class PasswordValidator
{
    protected $config;

    protected $errors = [];

    public function __construct()
    {
        // config/setting.php 파일의 password 설정을 읽음
        $this->config = config('setting.password') ?? [];
    }

    /**
     * 패스워드 유효성 검증
     *
     * @param  array  $userData  사용자 정보 (이름, 이메일 등)
     */
    public function validate(string $password, array $userData = []): bool
    {
        $this->errors = [];

        // 길이 체크
        $this->checkLength($password);

        // 대문자 체크
        if ($this->config['require_uppercase'] ?? false) {
            $this->checkUppercase($password);
        }

        // 소문자 체크
        if ($this->config['require_lowercase'] ?? false) {
            $this->checkLowercase($password);
        }

        // 숫자 체크
        if ($this->config['require_numbers'] ?? false) {
            $this->checkNumbers($password);
        }

        // 특수문자 체크
        if ($this->config['require_special_chars'] ?? false) {
            $this->checkSpecialChars($password);
        }

        // 공백 체크
        if (! ($this->config['allow_spaces'] ?? false)) {
            $this->checkSpaces($password);
        }

        // 강도 체크
        if ($this->config['strength']['check_sequential'] ?? false) {
            $this->checkSequential($password);
        }

        if ($this->config['strength']['check_repeated'] ?? false) {
            $this->checkRepeated($password);
        }

        // 사용자 정보와 유사성 체크
        if (($this->config['strength']['check_user_similarity'] ?? false) && ! empty($userData)) {
            $this->checkUserSimilarity($password, $userData);
        }

        // 일반적인 패스워드 체크
        if ($this->config['strength']['check_common_passwords'] ?? false) {
            $this->checkCommonPasswords($password);
        }

        return empty($this->errors);
    }

    /**
     * 검증 오류 메시지 반환
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 첫 번째 오류 메시지 반환
     */
    public function getFirstError(): ?string
    {
        return ! empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * 길이 체크
     */
    protected function checkLength(string $password): void
    {
        $minLength = $this->config['min_length'] ?? 8;
        $maxLength = $this->config['max_length'] ?? 128;

        if (strlen($password) < $minLength) {
            $this->errors[] = str_replace(':min', $minLength, $this->config['messages']['min_length'] ?? "비밀번호는 최소 {$minLength}자 이상이어야 합니다.");
        }

        if (strlen($password) > $maxLength) {
            $this->errors[] = str_replace(':max', $maxLength, $this->config['messages']['max_length'] ?? "비밀번호는 최대 {$maxLength}자를 초과할 수 없습니다.");
        }
    }

    /**
     * 대문자 포함 체크
     */
    protected function checkUppercase(string $password): void
    {
        if (! preg_match('/[A-Z]/', $password)) {
            $this->errors[] = $this->config['messages']['require_uppercase'] ?? '비밀번호는 최소 1개의 대문자를 포함해야 합니다.';
        }
    }

    /**
     * 소문자 포함 체크
     */
    protected function checkLowercase(string $password): void
    {
        if (! preg_match('/[a-z]/', $password)) {
            $this->errors[] = $this->config['messages']['require_lowercase'] ?? '비밀번호는 최소 1개의 소문자를 포함해야 합니다.';
        }
    }

    /**
     * 숫자 포함 체크
     */
    protected function checkNumbers(string $password): void
    {
        if (! preg_match('/[0-9]/', $password)) {
            $this->errors[] = $this->config['messages']['require_numbers'] ?? '비밀번호는 최소 1개의 숫자를 포함해야 합니다.';
        }
    }

    /**
     * 특수문자 포함 체크
     */
    protected function checkSpecialChars(string $password): void
    {
        $allowedChars = $this->config['allowed_special_chars'] ?? '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $pattern = '/['.preg_quote($allowedChars, '/').']/';

        if (! preg_match($pattern, $password)) {
            $this->errors[] = $this->config['messages']['require_special_chars'] ?? '비밀번호는 최소 1개의 특수문자를 포함해야 합니다.';
        }
    }

    /**
     * 공백 체크
     */
    protected function checkSpaces(string $password): void
    {
        if (strpos($password, ' ') !== false) {
            $this->errors[] = $this->config['messages']['no_spaces'] ?? '비밀번호에 공백을 포함할 수 없습니다.';
        }
    }

    /**
     * 연속된 문자/숫자 체크
     */
    protected function checkSequential(string $password): void
    {
        $sequences = [
            'abcdefghijklmnopqrstuvwxyz',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '0123456789',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        $lowerPassword = strtolower($password);

        foreach ($sequences as $sequence) {
            for ($i = 0; $i <= strlen($sequence) - 3; $i++) {
                $substr = substr($sequence, $i, 3);
                if (strpos($lowerPassword, $substr) !== false || strpos($lowerPassword, strrev($substr)) !== false) {
                    $this->errors[] = $this->config['messages']['sequential_chars'] ?? '연속된 문자나 숫자를 사용할 수 없습니다.';

                    return;
                }
            }
        }
    }

    /**
     * 반복된 문자 체크
     */
    protected function checkRepeated(string $password): void
    {
        $maxRepeated = $this->config['strength']['max_repeated_chars'] ?? 3;

        if (preg_match('/(.)\1{'.($maxRepeated - 1).',}/', $password)) {
            $this->errors[] = str_replace(':max', $maxRepeated,
                $this->config['messages']['repeated_chars'] ?? "동일한 문자를 {$maxRepeated}개 이상 연속으로 사용할 수 없습니다.");
        }
    }

    /**
     * 사용자 정보와 유사성 체크
     */
    protected function checkUserSimilarity(string $password, array $userData): void
    {
        $lowerPassword = strtolower($password);

        foreach ($userData as $key => $value) {
            if (! is_string($value) || strlen($value) < 3) {
                continue;
            }

            $lowerValue = strtolower($value);

            // 이메일인 경우 @ 앞부분만 체크
            if ($key === 'email' && strpos($value, '@') !== false) {
                $lowerValue = strtolower(explode('@', $value)[0]);
            }

            // 값이 패스워드에 포함되어 있는지 체크
            if (strlen($lowerValue) >= 3 && strpos($lowerPassword, $lowerValue) !== false) {
                $this->errors[] = $this->config['messages']['too_similar'] ?? '사용자 정보와 너무 유사한 비밀번호입니다.';

                return;
            }
        }
    }

    /**
     * 일반적인 패스워드 체크
     */
    protected function checkCommonPasswords(string $password): void
    {
        $commonPasswords = [
            'password', '12345678', '123456789', '12345', '1234567890',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            '1234567', 'password1', '123123', 'qwerty', 'abc123',
            'iloveyou', 'admin123', 'welcome123', 'root', 'toor',
            'pass', 'test', 'guest', 'master', 'dragon', 'baseball',
            'football', 'letmein123', 'michael', 'shadow', 'superman',
            'batman', '696969', '111111', '000000', 'access', 'passw0rd',
        ];

        $lowerPassword = strtolower($password);

        if (in_array($lowerPassword, $commonPasswords)) {
            $this->errors[] = $this->config['messages']['too_common'] ?? '너무 일반적인 비밀번호입니다.';
        }
    }

    /**
     * 패스워드 강도 계산
     *
     * @return int 1-5 (1: weak, 5: very strong)
     */
    public function calculateStrength(string $password): int
    {
        $strength = 0;

        // 길이에 따른 점수
        if (strlen($password) >= 8) {
            $strength++;
        }
        if (strlen($password) >= 12) {
            $strength++;
        }
        if (strlen($password) >= 16) {
            $strength++;
        }

        // 문자 종류에 따른 점수
        if (preg_match('/[a-z]/', $password)) {
            $strength++;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $strength++;
        }
        if (preg_match('/[0-9]/', $password)) {
            $strength++;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $strength++;
        }

        // 최대 5점으로 정규화
        return min(5, max(1, round($strength / 1.4)));
    }

    /**
     * 패스워드 생성
     */
    public function generate(?int $length = null): string
    {
        $generatorConfig = $this->config['generator'] ?? [];
        $length = $length ?? $generatorConfig['default_length'] ?? 16;

        $chars = '';

        if ($generatorConfig['include_lowercase'] ?? true) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }

        if ($generatorConfig['include_uppercase'] ?? true) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($generatorConfig['include_numbers'] ?? true) {
            $chars .= '0123456789';
        }

        if ($generatorConfig['include_special'] ?? true) {
            $chars .= $this->config['allowed_special_chars'] ?? '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }

        // 혼동하기 쉬운 문자 제거
        if ($generatorConfig['exclude_ambiguous'] ?? true) {
            $ambiguous = $generatorConfig['ambiguous_chars'] ?? '0O1lI';
            $chars = str_replace(str_split($ambiguous), '', $chars);
        }

        $password = '';
        $charsLength = strlen($chars);

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }

        // 생성된 패스워드가 규칙을 만족하는지 확인
        if (! $this->validate($password)) {
            // 규칙을 만족하지 않으면 재생성
            return $this->generate($length);
        }

        return $password;
    }

    /**
     * 패스워드 해싱
     */
    public function hash(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * 패스워드 검증
     */
    public function check(string $password, string $hashedPassword): bool
    {
        return Hash::check($password, $hashedPassword);
    }
}
