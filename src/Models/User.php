<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'uuid',
        'shard_id',
        'isAdmin',
        'utype',
        'last_login_at',
        'login_count',
        'password_changed_at',
        'password_expires_at',
        'password_expiry_days',
        'password_expiry_notified',
        // 2FA 관련 필드
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_method',
        'last_2fa_used_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isAdmin' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'password_expires_at' => 'datetime',
            'password_expiry_notified' => 'boolean',
            // 2FA 관련 필드
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'last_2fa_used_at' => 'datetime',
        ];
    }

    public function userType()
    {
        return $this->belongsTo(AdminUsertype::class, 'utype', 'code');
    }

    public function isAdmin()
    {
        return $this->isAdmin === true;
    }

    public function hasRole($role)
    {
        return $this->utype === $role;
    }

    public function getDisplayTypeAttribute()
    {
        if ($this->userType) {
            return $this->userType->name;
        }

        return $this->utype ?: 'User';
    }

    public function scopeAdmins($query)
    {
        return $query->where('isAdmin', true);
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('isAdmin', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('utype', $type);
    }

    public function isPasswordExpired()
    {
        if (! $this->password_expires_at) {
            return false;
        }

        return now()->greaterThan($this->password_expires_at);
    }

    public function isPasswordExpiringSoon($days = null)
    {
        if (! $this->password_expires_at) {
            return false;
        }

        $warningDays = $days ?? config('setting.password.expiry_warning_days', 7);
        $warningDate = now()->addDays($warningDays);

        return $this->password_expires_at->lessThanOrEqualTo($warningDate) &&
               $this->password_expires_at->greaterThan(now());
    }

    public function getDaysUntilPasswordExpiryAttribute()
    {
        if (! $this->password_expires_at) {
            return null;
        }

        $days = now()->diffInDays($this->password_expires_at, false);

        return $days;
    }

    public function getPasswordExpiryStatusAttribute()
    {
        if (! $this->password_expires_at) {
            return 'active';
        }

        if ($this->isPasswordExpired()) {
            return 'expired';
        }

        if ($this->isPasswordExpiringSoon()) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function updatePasswordExpiry()
    {
        $expiryDays = $this->password_expiry_days ?? config('setting.password.expiry_days', 90);

        if ($expiryDays > 0) {
            $this->password_changed_at = now();
            $this->password_expires_at = now()->addDays($expiryDays);
            $this->password_expiry_notified = false;
        } else {
            $this->password_changed_at = now();
            $this->password_expires_at = null;
            $this->password_expiry_notified = false;
        }

        return $this;
    }
}
