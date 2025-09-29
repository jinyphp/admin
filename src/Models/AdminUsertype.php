<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUsertype extends Model
{
    use HasFactory;

    protected $table = 'admin_user_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'level',
        'enable',
        'pos',
        'permissions',
        'settings',
        'badge_color',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'permissions' => 'array',
        'settings' => 'array',
    ];
}
